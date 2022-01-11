<?php

namespace Drupal\custom_rest_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a Custom Resource
 *
 * @RestResource(
 *   id = "custom_resource",
 *   label = @Translation("Custom Resource"),
 *   uri_paths = {
 *     "canonical" = "/custom/api/{id}"
 *   }
 * )
 */
class CustomResource extends ResourceBase {
    /**
   * A current user instance which is logged in the session.
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
    protected $loggedUser;

    /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $config
   *   A configuration array which contains the information about the plugin instance.
   * @param string $module_id
   *   The module_id for the plugin instance.
   * @param mixed $module_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A currently logged user instance.
   */
    public function __construct(
        array $config,
        $module_id,
        $module_definition,
        array $serializer_formats,
        LoggerInterface $logger,
        AccountProxyInterface $current_user) {
        parent::__construct($config, $module_id, $module_definition, $serializer_formats, $logger);

        $this->loggedUser = $current_user;
    }

    /**
   * {@inheritdoc}
   */
    public static function create(ContainerInterface $container, array $config, $module_id, $module_definition) {
        return new static(
        $config,
        $module_id,
        $module_definition,
        $container->getParameter('serializer.formats'),
        $container->get('logger.factory')->get('custom_rest_api'),
        $container->get('current_user')
        );
    }

    /**
   * Responds to entity GET requests.
   * Get all nodes for requested user(id)
   * @return \Drupal\rest\ResourceResponse
   */
    public function get($id=null) {
        if(!$this->loggedUser->hasPermission('access content')) {
            throw new AccessDeniedHttpException();
        }	
        if($id) {
            
            $nids = \Drupal::entityQuery('node')->condition('uid', $id)->execute();
            if($nids) {
                $nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);
                    foreach ($nodes as $key => $value) {
                        $data[] = ['id' => $value->id(),'title' => $value->getTitle(), 'type'=> $value->getType() ];
                }
            }
            else{
                $data[] = ['message' => 'No data found.'];
            }
        }
        $response = new ResourceResponse($data);
        return $response;
    }
        

    

}
