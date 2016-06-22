<?php namespace KurtJensen\MyCalendar\Components;

use Carbon\Carbon;
use Cms\Classes\ComponentBase;
use Lang;

class Month extends ComponentBase
{
    public $month;
    public $year;
    public $dayprops;
    public $color;
    public $events;
    public $calHeadings;

    public $monthTitle;
    public $monthNum;
    public $running_day;
    public $days_in_month;
    public $dayPointer;
    public $prevMonthLastDay;
    public $prevMonthMonday;

    public $linkNextMonth;
    public $linkPrevMonth;

    public function componentDetails()
    {
        return [
            'name' => 'kurtjensen.mycalendar::lang.month.name',
            'description' => 'kurtjensen.mycalendar::lang.month.description',
        ];
    }

    public function defineProperties()
    {
        return [
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
            'events' => [
                'title' => 'kurtjensen.mycalendar::lang.month.events_title',
                'description' => 'kurtjensen.mycalendar::lang.month.events_description',
            ],
            'color' => [
                'title' => 'kurtjensen.mycalendar::lang.month.color_title',
                'description' => 'kurtjensen.mycalendar::lang.month.color_description',
                'type' => 'dropdown',
                'default' => 'red',
            ],
            'dayprops' => [
                'title' => 'kurtjensen.mycalendar::lang.month.dayprops_title',
                'description' => 'kurtjensen.mycalendar::lang.month.dayprops_description',
            ],
            'loadstyle' => [
                'title' => 'kurtjensen.mycalendar::lang.month.loadstyle_title',
                'description' => 'kurtjensen.mycalendar::lang.month.loadstyle_description',
                'type' => 'dropdown',
                'default' => '1',
                'options' => [
                    0 => 'kurtjensen.mycalendar::lang.month.opt_no',
                    1 => 'kurtjensen.mycalendar::lang.month.opt_yes',
                ],
            ],
        ];
    }

    public function trans($string)
    {
        return Lang::get($string);
    }

    public function getColorOptions()
    {
        $colors = [
            'red' => Lang::get('kurtjensen.mycalendar::lang.month.color_red'),
            'green' => Lang::get('kurtjensen.mycalendar::lang.month.color_green'),
            'blue' => Lang::get('kurtjensen.mycalendar::lang.month.color_blue'),
            'yellow' => Lang::get('kurtjensen.mycalendar::lang.month.color_yellow'),
        ];
        return $colors;
    }

    public function onRender()
    {
        if ($this->property('loadstyle')) {
            $this->addCss('/plugins/kurtjensen/mycalendar/assets/css/calendar.css');
        }
        $this->month = in_array($this->property('month'), range(1, 12)) ? $this->property('month') : date('m');
        $this->year = in_array($this->property('year'), range(2014, 2030)) ? $this->property('year') : date('Y');
        $this->calcElements();
        $this->dayprops = $this->property('dayprops');
        $this->color = $this->property('color');
        $this->events = $this->property('events');
    }

    public function calcElements()
    {

        $this->calHeadings = [
            Lang::get('kurtjensen.mycalendar::lang.month.day_sun'),
            Lang::get('kurtjensen.mycalendar::lang.month.day_mon'),
            Lang::get('kurtjensen.mycalendar::lang.month.day_tue'),
            Lang::get('kurtjensen.mycalendar::lang.month.day_wed'),
            Lang::get('kurtjensen.mycalendar::lang.month.day_thu'),
            Lang::get('kurtjensen.mycalendar::lang.month.day_fri'),
            Lang::get('kurtjensen.mycalendar::lang.month.day_sat'),
        ];
        $time = new Carbon($this->month . '/1/' . $this->year);

        $time->copy();

        $this->monthTitle = $time->format('F');
        $this->monthNum = $time->month;
        $this->running_day = $time->dayOfWeek;
        $this->days_in_month = $time->daysInMonth;
        $this->dayPointer = 0 - $this->running_day;
        $prevMonthLastDay = $time->copy()->subMonth()->daysInMonth;
        $this->prevMonthMonday = $this->dayPointer + $prevMonthLastDay + 1;

        $this->linkNextMonth = $time->copy()->addDays(32);

        $this->linkPrevMonth = $time->copy()->subDays(2);

    }

}
