<?php

namespace DsTrinityDataBundle\Resource\ProxyResolver;

use DsTrinityDataBundle\DsTrinityDataEvents;
use DsTrinityDataBundle\Event\DataProxyEvent;
use DynamicSearchBundle\Resource\Proxy\ProxyResource;
use Pimcore\Model\DataObject;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectProxyResolver implements ProxyResolverInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['fetch_variant_parent_until_reach_object']);
        $resolver->setAllowedTypes('fetch_variant_parent_until_reach_object', ['bool']);
        $resolver->setDefaults([
            'fetch_variant_parent_until_reach_object' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function resolveProxy(ElementInterface $resource, array $proxyOptions, array $contextDefinitionOptions)
    {
        if (!$resource instanceof DataObject) {
            return null;
        }

        if ($proxyOptions['fetch_variant_parent_until_reach_object'] === false) {
            return null;
        }

        if ($resource->getType() !== DataObject\AbstractObject::OBJECT_TYPE_VARIANT) {
            return null;
        }

        $proxyObject = $resource;
        while ($proxyObject->getType() === DataObject\AbstractObject::OBJECT_TYPE_VARIANT) {
            $proxyObject = $proxyObject->getParent();
        }

        $proxyResource = new ProxyResource($resource, $contextDefinitionOptions['contextDispatchType'], $contextDefinitionOptions['contextName']);
        $proxyResource->setProxyResource($proxyObject);

        $proxyEvent = new DataProxyEvent('object', $proxyResource);
        $this->eventDispatcher->dispatch($proxyEvent, DsTrinityDataEvents::PROXY_DEFAULT_DATA_OBJECT);

        return $proxyEvent->getProxyResource();
    }
}
