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
        $this->weather = null;
    }

    public function prepare($weatherJson) {
        $weather = new WeatherCollection();

        if (!property_exists($weatherJson, 'timezone')) {
            return $weather;
        }
        $units = $this->configManager->get('weather.units', 'metric');

        if (property_exists($weatherJson, 'current')) {
            $weatherJson->current->main = $weatherJson;
            $w = new WeatherData($weatherJson->current, $units);
            $weather->setCurrent($w);
        }
        if (property_exists($weatherJson, 'daily')) {
            foreach($weatherJson->daily as $day) {
                $day->main = $weatherJson;
                $w = new WeatherData($day, $units);
                $weather->setDaily($w);
            }
        }
        if (property_exists($weatherJson, 'hourly')) {
            foreach($weatherJson->hourly as $hour) {
                $hour->main = $weatherJson;
                $w = new WeatherData($hour, $units);
                $weather->setHourly($w);
            }
        }

        return $weather;
    }

    public function get() {
        if (!is_null($this->weather)) {
            return $this->weather;
        }

        $success = false;
        $key = 'weather_'.md5(serialize($this->getParams()));
        $weather = $this->cache->getItem($key, $success);
        if ($success) {
            $this->weather = $weather;
            return $weather;
        }

        $weatherJson = $this->load();
        $weather = $this->prepare($weatherJson);
        $this->cache->setItem($key, $weather);
        $this->weather = $weather;
        return $weather;
    }

    protected function getParams() {
        return array(
            'lat' => $this->configManager->get('weather.lat'),
            'lon' => $this->configManager->get('weather.lon'),
            'exclude' => 'minutely',
            'units' => $this->configManager->get('weather.units', 'metric'),
            'lang' => explode('-', $this->configManager->get('i18n.locale', $this->configManager->get('weather.lang', 'en')))[0],
            'appid' => $this->configManager->get('weather.appid'),
        );
    }

    public function load() {
        $client = new \Zend\Http\Client('https://api.openweathermap.org/data/3.0/onecall', array(
            'timeout' => 10,
        ));
        $client->setParameterGet($this->getParams());
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
