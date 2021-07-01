<?php

namespace Weather\Service;

use Base\Manager\ConfigManager;
use Base\Manager\OptionManager;
use Base\Service\AbstractService;
use DateTime;
use Exception;
use RuntimeException;
use Zend\ServiceManager\ServiceLocatorInterface;

class WeatherService extends AbstractService
{
    protected $configManager;
    protected $optionManager;
    protected $cache;
    protected $weather;

    public function __construct(
        ConfigManager $configManager,
        OptionManager $optionManager)
    {
        $this->configManager = $configManager;
        $this->optionManager = $optionManager;
        $this->cache = \Zend\Cache\StorageFactory::factory(
            array(
                'adapter' => array(
                    'name' => 'filesystem',
                    'options' => array(
                        'cacheDir' => getcwd() . '/data/cache/',
                        'namespace' => 'weather',
                        'ttl' => 3600,
                    ),
                ),
                'plugins' => array('serializer'),
            )
        );
    }

    public function prepare($weatherJson) {
        $weather = new WeatherCollection();

        if (!property_exists($weatherJson, 'timezone')) {
            return $weather;
        }
        $timezone = new \DateTimeZone($weatherJson->timezone);

        if (property_exists($weatherJson, 'list')) {
            foreach($weatherJson->list as $day) {
                $w = new WeatherData();
                $w->dt = new DateTime('@'.$day->dt);
                $w->dt->setTimezone($timezone);
                $w->temperature = $day->temp->day;
                $w->description = $day->weather[0]->description;
                $w->icon = $day->weather[0]->icon;
                $weather->setDaily($w);
            }
        }
        if (property_exists($weatherJson, 'daily')) {
            foreach($weatherJson->daily as $day) {
                $w = new WeatherData();
                $w->dt = new DateTime('@'.$day->dt);
                $w->dt->setTimezone($timezone);
                $w->temperature = $day->temp->day;
                $w->description = $day->weather[0]->description;
                $w->icon = $day->weather[0]->icon;
                $weather->setDaily($w);
            }
        }
        if (property_exists($weatherJson, 'current')) {
            $w = new WeatherData();
            $w->dt = new DateTime('@'.$weatherJson->current->dt);
            $w->dt->setTimezone($timezone);
            $w->temperature = $weatherJson->current->temp;
            $w->description = $weatherJson->current->weather[0]->description;
            $w->icon = $weatherJson->current->weather[0]->icon;
            $weather->setDaily($w);
        }
        if (property_exists($weatherJson, 'hourly')) {
            foreach($weatherJson->hourly as $hour) {
                $w = new WeatherData();
                $w->dt = new DateTime('@'.$hour->dt);
                $w->dt->setTimezone($timezone);
                $w->temperature = $hour->temp;
                $w->description = $hour->weather[0]->description;
                $w->icon = $hour->weather[0]->icon;
                $weather->setHourly($w);
            }
        }

        return $weather;
    }

    public function get() {
        $success = false;
        $weather = $this->cache->getItem('weather', $success);
        if ($success) {
            return $weather;
        }

        $weatherJson = $this->load();
        $weather = $this->prepare($weatherJson);
        $this->cache->setItem('weather', $weather);
        return $weather;
    }

    public function load() {
        $client = new \Zend\Http\Client('https://api.openweathermap.org/data/2.5/onecall', array(
            'timeout' => 10,
        ));
        $client->setParameterGet(
            array(
                'lat' => $this->configManager->get('weather.lat'),
                'lon' => $this->configManager->get('weather.lon'),
                'exclude' => 'minutely',
                'units' => $this->configManager->get('weather.units', 'metric'),
                'lang' => $this->configManager->get('weather.lang', 'en'),
                'appid' => $this->configManager->get('weather.appid'),
            )
        );
        try {
            $response = $client->send();
            if ($response->isSuccess()) {
                return \Zend\Json\Json::decode($response->getBody());
            }
        }
        catch (\Zend\Http\Exception\RuntimeException $e) {
        }
        return new \stdClass();
    }

    

}
