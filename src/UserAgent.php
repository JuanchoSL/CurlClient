<?php

declare(strict_types=1);

namespace JuanchoSL\CurlClient;

class UserAgent
{

    private object $user_agents;

    public function __construct()
    {
        $file_path = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . "etc" . DIRECTORY_SEPARATOR . "user_agents.json";
        if (!file_exists($file_path)) {
            throw new \Exception("File not found", 404);
        }
        $file_contents = file_get_contents($file_path);
        if (empty($file_contents)) {
            throw new \Exception("An error ocurred reading the file", 500);
        }
        $this->user_agents = json_decode($file_contents, false);
    }

    /**
     * Retrieve all the user agents collection, group by platform
     * @return object User agent collection
     */
    public function getUserAgents(): object
    {
        return $this->user_agents;
    }

    /**
     * Retrieve all the desktop user agents collection, group by SOs
     * @return object User agent desktop collection
     */
    public function getDesktops(): object
    {
        return $this->getUserAgents()?->desktop ?? new \stdClass;
    }

    /**
     * Retrieve all the mobile user agents collection, group by SOs
     * @return object User agent mobile collection
     */
    public function getMobiles(): object
    {
        return $this->getUserAgents()?->mobile ?? new \stdClass;
    }

    /**
     * Retrieve all the Windows desktop user agents
     * @return array<int,string> User agent Windows desktop
     */
    public function getDesktopsWindows(): array
    {
        return $this->getDesktops()?->windows ?? [];
    }

    /**
     * Retrieve all the Mac desktop user agents array
     * @return array<int,string> User agent Mac desktop array
     */
    public function getDesktopsMac(): array
    {
        return $this->getDesktops()?->mac ?? [];
    }

    /**
     * Retrieve all the Linux desktop user agents array
     * @return array<int,string> User agent Linux desktop array
     */
    public function getDesktopsLinux(): array
    {
        return $this->getDesktops()?->linux ?? [];
    }

    /**
     * Retrieve all the Iphone mobile user agents array
     * @return array<int,string> User agent Iphone desktop array
     */
    public function getMobilesIphone(): array
    {
        return $this->getMobiles()?->iphone ?? [];
    }

    /**
     * Retrieve all the Ipad mobile user agents array
     * @return array<int,string> User agent Ipad mobile array
     */
    public function getMobilesIpad(): array
    {
        return $this->getMobiles()?->ipad ?? [];
    }

    /**
     * Retrieve all the Ipod mobile user agents array
     * @return array<int,string> User agent Ipod mobile array
     */
    public function getMobilesIpod(): array
    {
        return $this->getMobiles()?->ipod ?? [];
    }

    /**
     * Retrieve all the Android mobile user agents array
     * @return array<int,string> User agent Android mobile array
     */
    public function getMobilesAndroid(): array
    {
        return $this->getMobiles()?->android ?? [];
    }

    public function getDesktopWindows(?int $index = null): string
    {
        return $this->getUserAgent($this->getDesktopsWindows(), $index);
    }

    public function getDesktopMac(?int $index = null): string
    {
        return $this->getUserAgent($this->getDesktopsMac(), $index);
    }

    public function getDesktopLinux(?int $index = null): string
    {
        return $this->getUserAgent($this->getDesktopsLinux(), $index);
    }

    public function getMobileIphone(?int $index = null): string
    {
        return $this->getUserAgent($this->getMobilesIphone(), $index);
    }

    public function getMobileIpad(?int $index = null): string
    {
        return $this->getUserAgent($this->getMobilesIpad(), $index);
    }

    public function getMobileIpod(?int $index = null): string
    {
        return $this->getUserAgent($this->getMobilesIpod(), $index);
    }

    public function getMobileAndroid(?int $index = null): string
    {
        return $this->getUserAgent($this->getMobilesAndroid(), $index);
    }

    /**
     *
     * @param array<int,string> $elements
     * @param int $index
     * @return string
     */
    protected function getUserAgent(array $elements, ?int $index = null): string
    {
        return (string) (is_null($index) || !array_key_exists($index, $elements)) ? array_key_last($elements) . '' : $elements[$index];
    }

}