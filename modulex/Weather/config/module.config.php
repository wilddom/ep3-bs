<?php

return array(
    'service_manager' => array(
        'factories' => array(
            'Weather\Service\WeatherService' => 'Weather\Service\WeatherServiceFactory',
        ),
    ),

    'view_helpers' => array(
        'invokables' => array(
            'WeatherInfo' => 'Weather\View\Helper\WeatherInfo',
        ),

        'factories' => array(
            'WeatherCell' => 'Weather\View\Helper\WeatherCellFactory',
            'WeatherHourly' => 'Weather\View\Helper\WeatherHourlyFactory',
        ),
    ),
);
