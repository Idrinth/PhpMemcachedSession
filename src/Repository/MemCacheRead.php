<?php

namespace YetAnotherWebStack\PhpMemcachedSession\Repository;

class MemCacheRead implements \YetAnotherWebStack\PhpMemcachedSession\Interfaces\Repository {

    /**
     *
     * @var int
     */
    protected $duration = 3600;

    /**
     *
     * @var string[]
     */
    protected $prefix = ['yet-another-web-stack', 'memcached-session'];

    /**
     *
     * @var \Memcached
     */
    protected $memcache;

    /**
     * parma \Memcached $memcache
     */
    public function __construct(\Memcached $memcache) {
        $this->memcache = $memcache;
        $this->memcache->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
        $this->setMemcacheLogin();
        $this->initializeServer();
        $this->duration = DependencyInjector::get(
                        'YetAnotherWebStack\PhpMemcachedSession\Interfaces\Configuration'
                )->getGeneral("gc_maxlifetime");
        if (!$this->duration) {
            $this->duration = 3600;
        }
        return $this;
    }

    /**
     * add login data if provided
     */
    protected function setMemcacheLogin() {
        if (\YetAnotherWebStack\PhpMemcachedSession\Service\DependencyInjector::get(
                        'YetAnotherWebStack\PhpMemcachedSession\Interfaces\Configuration'
                )->getSpecific('memcache_user') &&
                \YetAnotherWebStack\PhpMemcachedSession\Service\DependencyInjector::get(
                        'YetAnotherWebStack\PhpMemcachedSession\Interfaces\Configuration'
                )->getSpecific('memcache_password')) {
            $this->memcache->setSaslAuthData(
                    \YetAnotherWebStack\PhpMemcachedSession\Service\DependencyInjector::get(
                            'YetAnotherWebStack\PhpMemcachedSession\Interfaces\Configuration'
                    )->getSpecific('memcache_user'),
                    \YetAnotherWebStack\PhpMemcachedSession\Service\DependencyInjector::get(
                            'YetAnotherWebStack\PhpMemcachedSession\Interfaces\Configuration'
                    )->getSpecific('memcache_password')
            );
        }
    }

    /**
     * initializes a new server if necessary
     */
    protected function initializeServer() {
        if (count($this->memcache->getServerList()) == 0) {
            $this->memcache->addServer(
                    \YetAnotherWebStack\PhpMemcachedSession\Service\DependencyInjector::get(
                            'YetAnotherWebStack\PhpMemcachedSession\Interfaces\Configuration'
                    )->getSpecific('memcache_server'),
                    \YetAnotherWebStack\PhpMemcachedSession\Service\DependencyInjector::get(
                            'YetAnotherWebStack\PhpMemcachedSession\Interfaces\Configuration'
                    )->getSpecific('memcache_port')
            );
        }
    }

    /**
     *
     * @param string[] $params
     * @return string
     */
    protected function getKey($params = array()) {
        return trim(
                implode('.', $this->prefix) . '.' . implode('.', $params), '.'
        );
    }

    /**
     *
     * @param array $params
     * @return string
     */
    public function getByKey(array $params) {
        $value = $this->memcache->get($this->getKey($params));
        if ($value) {
            $this->memcache->touch($this->getKey($params),
                    time() + $this->duration);
        }
        $unserializer = \YetAnotherWebStack\PhpMemcachedSession\Service\DependencyInjector::get(
                        'YetAnotherWebStack\PhpMemcachedSession\Interfaces\Configuration'
                )->getSpecific('unserializer');
        if ($unserializer && is_callable($unserializer)) {
            $value = serialize(call_user_func($unserializer, $value));
        }
        return $value;
    }

    /**
     *
     * @param string[] $params
     * @param string $value
     * @return boolean
     */
    public function setByKey(array $params, $value) {
        return false;
    }

    /**
     *
     * @param string[] $params
     * @return boolean
     */
    public function removeByKey(array $params) {
        return false;
    }

}
