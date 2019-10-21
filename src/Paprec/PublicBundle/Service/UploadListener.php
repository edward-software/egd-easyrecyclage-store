<?php
/**
 * Created by PhpStorm.
 * User: frede
 * Date: 13/11/2018
 * Time: 11:38
 */

namespace Paprec\PublicBundle\Service;


use Doctrine\Common\Persistence\ObjectManager;
use Oneup\UploaderBundle\Event\PostPersistEvent;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;


class UploadListener
{
    /**
     * @var ObjectManager
     */
    private $om;

    private $container;

    public function __construct(ObjectManager $om, ContainerInterface $container)
    {
        $this->om = $om;
        $this->container = $container;
    }

    public function onUpload(PostPersistEvent $event)
    {
        try {
            /**
             * On récupère le dossier où enregistrer les fichiers
             * Si le dossier = 'null' alors on créé un nouveau dossier que l'on retournera
             */
            $request = $event->getRequest();
            $dirName = $request->get('dir');
            if ($dirName === 'null') {
                $dirName = Uuid::uuid4();
            }

            /**
             * On déplace le fichier dans le dir
             */
            $file = $event->getFile();
            $file->move(dirname($file) . '/' . $dirName, basename($file));


            //if everything went fine
            $response = $event->getResponse();
            $response['dir'] = $dirName;
            $response['filename'] = basename($file);

            return $response;
        } catch (\Exception $e) {

        }
    }
}
