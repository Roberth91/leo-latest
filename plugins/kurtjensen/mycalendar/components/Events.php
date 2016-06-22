<?php namespace KurtJensen\MyCalendar\Components;

use Auth;
use Carbon\Carbon;
use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use KurtJensen\MyCalendar\Models\Event as MyEvents;
use KurtJensen\MyCalendar\Models\Occurrence as Ocurrs;
use KurtJensen\MyCalendar\Models\Settings;
use Lang;

class Events extends ComponentBase
{
    //use \KurtJensen\MyCalendar\Traits\LoadPermissions;

    public $month;
    public $year;
    public $usePermissions = 0;
    public $dayspast = 0;
    public $daysfuture = 0;
    public $compLink = 'Events';
    public $user_id = null;

    public function componentDetails()
    {
        return [
            'name' => 'kurtjensen.mycalendar::lang.events_comp.name',
            'description' => 'kurtjensen.mycalendar::lang.events_comp.description',
        ];
    }

    public function defineProperties()
    {
        return [
            'linkpage' => [
                'title' => 'kurtjensen.mycalendar::lang.events_comp.linkpage_title',
                'description' => 'kurtjensen.mycalendar::lang.events_comp.linkpage_desc',
                'type' => 'dropdown',
                'default' => '',
                'group' => 'kurtjensen.mycalendar::lang.events_comp.linkpage_group',
            ],
            'title_max' => [
                'title' => 'kurtjensen.mycalendar::lang.events_comp.title_max_title',
                'description' => 'kurtjensen.mycalendar::lang.events_comp.title_max_description',
                'default' => 100,
            ],
            'usePermissions' => [
                'title' => 'kurtjensen.mycalendar::lang.events_comp.permissions_title',
                'description' => 'kurtjensen.mycalendar::lang.events_comp.permissions_description',
                'type' => 'dropdown',
                'default' => 0,
                'options' => [
                    0 => 'kurtjensen.mycalendar::lang.events_comp.opt_no',
                    1 => 'kurtjensen.mycalendar::lang.events_comp.opt_yes',
                ],
            ],
            'month' => [
                'title' => 'kurtjensen.mycalendar::lang.month.month_title',
                'description' => 'kurtjensen.mycalendar::lang.month.month_description',
                'default' => '{{ :month }}',
            ],
            'year' => [
                'title' => 'kurtjensen.mycalendar::lang.month.year_title',
                'description' => 'kurtjensen.mycalendar::lang.month.year_description',
                'default' => '{{ :year }}',
            ],
            'dayspast' => [
                'title' => 'kurtjensen.mycalendar::lang.events_comp.past_title',
                'description' => 'kurtjensen.mycalendar::lang.events_comp.past_description',
                'default' => 0,
            ],
            'daysfuture' => [
                'title' => 'kurtjensen.mycalendar::lang.events_comp.future_title',
                'description' => 'kurtjensen.mycalendar::lang.events_comp.future_description',
                'default' => 60,
            ],
            'raw_data' => [
                'title' => 'kurtjensen.mycalendar::lang.events_comp.raw_data_title',
                'description' => 'kurtjensen.mycalendar::lang.events_comp.raw_data_description',
                'type' => 'dropdown',
                'default' => 0,
                'options' => [
                    0 => 'kurtjensen.mycalendar::lang.events_comp.opt_no',
                    1 => 'kurtjensen.mycalendar::lang.events_comp.opt_yes',
                ],
            ],
        ];
    }

    public function getLinkpageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName')+
        ['' => Lang::get('kurtjensen.mycalendar::lang.events_comp.linkpage_opt_none')];
    }

    public function init()
    {
        $this->usePermissions = $this->property('usePermissions', 0);
        $this->dayspast = $this->property('dayspast', 0);
        $this->daysfuture = $this->property('daysfuture', 60);

        $this->month = in_array($this->property('month'), range(1, 12)) ? $this->property('month') : date('m');
        $this->year = in_array($this->property('year'), range(2014, 2030)) ? $this->property('year') : date('Y');
    }

    public function onRun()
    {
        $this->page['MyEvents'] = $this->loadEvents();
    }

    public function userId()
    {
        if (is_null($this->user_id)) {
            $user = Auth::getUser();
        }

        if ($user) {
            $this->user_id = $user->id;
        } else {
            $this->user_id = 0;
        }
        return $this->user_id;
    }

    public function loadEvents()
    {
        if ($this->daysfuture || $this->dayspast) {
            $month_start = new Carbon(date('Y/m/d') . ' 00:00:00');
            $month_end = $month_start->copy()->addDays($this->daysfuture);
            $month_start->subDays($this->dayspast);
        } else {
            $month_start = new Carbon($this->year . '/' . $this->month . '/1 00:00:00');
            $month_end = $month_start->copy()->addMonth(1);
        }

        $MyEvents = [];
        $timeFormat = Settings::get('time_format', 'g:i a');

        $occurs = Ocurrs::where('start_at', '<', $month_end)->
        where('end_at', '>=', $month_start)->
        where('relation', 'events')->
        orderBy('start_at', 'ASC')->
        get();

        if (!$occurs) {
            return [];
        }

        $eventIds = $occurs->lists('event_id', 'id');

        $query = MyEvents::withOwner()
            ->published()
            ->whereIn('id', $eventIds);

        if ($this->usePermissions) {

            $query->permisions(
                $this->userId(),
                [Settings::get('public_perm')],
                Settings::get('deny_perm')
            );
        }

        $events = $query->get();

        $maxLen = $this->property('title_max', 100);
        $linkPage = $this->property('linkpage', '');

        foreach ($occurs as $occ) {
            if (!$e = $events->find($occ->event_id)) {
                continue;
            }

            $title = (strlen($e->text) > 50) ? substr(strip_tags($e->text), 0, $maxLen) . '...' : $e->text;

            $link = $e->link ? $e->link : ($linkPage ? Page::url($linkPage, ['slug' => $e->id]) :
                '#EventDetail"
            	data-request="onShowEvent"
            	data-request-data="evid:' . $occ->id . '"
            	data-request-update="\'' . $this->compLink . '::details\':\'#EventDetail\'" data-toggle="modal" data-target="#myModal');

            $time = $occ->is_allday ? '(' . Lang::get('kurtjensen.mycalendar::lang.occurrence.is_allday') . ')'
            : $occ->start_at->format($timeFormat);

            $MyEvents[$occ->start_at->year][$occ->start_at->month][$occ->start_at->day][] = [
                'name' => $e->name . ' ' . $time,
                'title' => $title,
                'link' => $link,
                'id' => $occ->id,
                'owner' => $e->user_id,
                'owner_name' => $e->owner_name,
                'data' => $e,
            ];
        }
        return $MyEvents;

    }

    public function onShowEvent()
    {
        $e = false;
        $ocurrs = Ocurrs::where('relation', 'events')->find(post('evid'));

        if ($ocurrs) {
            if ($this->usePermissions) {
                $query = MyEvents::withOwner()
                    ->permisions(
                        $this->userId(),
                        [Settings::get('public_perm')],
                        Settings::get('deny_perm')
                    );
            } else {

                $query = MyEvents::withOwner();
            }

            $e = $query->with('categorys')
                       ->where('is_published', true)
                       ->find($ocurrs->event_id);
        }
        if (!$e) {
            return $this->page['ev'] = ['name' => Lang::get('kurtjensen.mycalendar::lang.event.error_not_found'), 'cats' => []];
        }

        $timeFormat = Settings::get('time_format', 'g:i a');
        $dateFormat = Settings::get('date_format', 'F jS, Y');

        if ($this->property('raw_data', false)) {
            return $this->page['ev_data'] = [
                'ev' => $e,
                'oc' => $ocurrs,
                'format' => ['t' => $timeFormat, 'd' => $dateFormat],
            ];
        } else {
            return $this->page['ev'] = [
                'name' => $e->name,
                'date' => $ocurrs->start_at->format($dateFormat),
                'time' => $ocurrs->is_allday ? Lang::get('kurtjensen.mycalendar::lang.occurrence.is_allday')
                : $ocurrs->start_at->format($timeFormat) . ' - ' . $ocurrs->end_at->format($timeFormat),
                'link' => $e->link ? $e->link : '',
                'text' => $e->text,
                'cats' => $e->categorys->lists('name'),
                'owner' => $e->user_id,
                'owner_name' => $e->owner_name,
                'data' => $e,
            ];
        }
    }
}
