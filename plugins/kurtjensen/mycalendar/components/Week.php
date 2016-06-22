<?php namespace KurtJensen\MyCalendar\Components;

use Cms\Classes\ComponentBase;

class Week extends ComponentBase
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

    public function componentDetails()
    {
        return [
            'name' => 'kurtjensen.mycalendar::lang.week.name',
            'description' => 'kurtjensen.mycalendar::lang.week.description',
        ];
    }

    public function defineProperties()
    {
        return [
            'month' => [
                'title' => 'Month',
                'description' => 'The month you want to show.',
            ],
            'year' => [
                'title' => 'Year',
                'description' => 'The year you want to show.',
            ],
            'events' => [
                'title' => 'Events',
                'description' => 'Array of the events you want to show.',
            ],
            'color' => [
                'title' => 'Calendar Color',
                'description' => 'Array of the events you want to show.',
                'type' => 'dropdown',
                'default' => 'red',
            ],
            'dayprops' => [
                'title' => 'Day Properties',
                'description' => 'Array of the properties you want to put on the day indicator.',
            ],
            'loadstyle' => [
                'title' => 'Load Style Sheet',
                'description' => 'Load the default CSS file.',
                'type' => 'dropdown',
                'default' => '1',
                'options' => [0 => 'No', 1 => 'Yes'],
            ],
        ];
    }

    public function getColorOptions()
    {
        return ['red' => 'red', 'green' => 'green', 'blue' => 'blue', 'yellow' => 'yellow'];
    }

    public function onRender()
    {
        if ($this->property('loadstyle')) {
            $this->addCss('/plugins/kurtjensen/mycalendar/assets/css/calendar.css');
        }

        $this->month = $this->property('month', date('m'));
        $this->year = $this->property('year', date('Y'));
        $this->calcElements();
        $this->dayprops = $this->property('dayprops');
        $this->color = $this->property('color');
        $this->events = $this->property('events');
    }

    public function calcElements()
    {

        $this->calHeadings = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $time = strtotime($this->month . '/1/' . $this->year);
        $this->monthTitle = date('F', $time);
        $this->monthNum = date('n', $time);
        $this->running_day = date('w', $time);
        $this->days_in_month = date('t', $time);
        $this->dayPointer = 0 - $this->running_day;
    }

}
