<?php
namespace Maurisource\MageShip\Model\Cache\Type;

class MageShip extends \Magento\Framework\Cache\Frontend\Decorator\TagScope
{
    private $cacheState;
    private $cacheFrontendPool;

    /**
     * Cache type code unique among all cache types
     */
    const TYPE_IDENTIFIER = 'mageship';

    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    const CACHE_TAG = 'MAGESHIP_DATA';

    /**
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool
     */
    public function __construct(
        \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool,
        \Magento\Framework\App\Cache\StateInterface $cacheState
    ) {
        $this->cacheState = $cacheState;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    /**
     * Retrieve cache frontend instance being decorated
     *
     * @return \Magento\Framework\Cache\FrontendInterface
     */
    protected function _getFrontend()
    {
        $frontend = parent::_getFrontend();
        if (!$frontend) {
            $frontend = $this->cacheFrontendPool->get(self::TYPE_IDENTIFIER);
            $this->setFrontend($frontend);
        }

        return $frontend;
    }

    /**
     * Retrieve cache tag name
     *
     * @return string
     */
    public function getTag()
    {
        return self::CACHE_TAG;
    }

    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        if (!$this->isEnabled()) {
            return false;
        }

        return parent::save($data, $identifier, $tags, $lifeTime);
    }

    public function load($identifier)
    {
        if (!$this->isEnabled()) {
            return false;
        }

        return parent::load($identifier);
    }

    /**
     * @return bool
     */
    private function isEnabled()
    {
        return $this->cacheState->isEnabled(self::TYPE_IDENTIFIER);
    }
}
