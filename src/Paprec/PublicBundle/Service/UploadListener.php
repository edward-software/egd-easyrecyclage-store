<?php
/**
 * Created by PhpStorm.
 * User: frede
 * Date: 13/11/2018
 * Time: 11:38
 */

namespace Paprec\PublicBundle\Service;


use Doctrine\Common\Persistence\ObjectManager;
use Monolog\Logger;
use Oneup\UploaderBundle\Event\PostPersistEvent;
use Oneup\UploaderBundle\Event\PostUploadEvent;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;


class UploadListener
{
    /**
     * @var ObjectManager
     */
    private $om;

    private $container;
    private $logger;

    public function __construct(ObjectManager $om, ContainerInterface $container)
    {
        $this->om = $om;
        $this->container = $container;
        $this->logger = $logger;
    }

    public function onUpload(PostPersistEvent $event)
    {
        try {
            /**
             * Nettoyage du dossier web/uploads/gallery
             */
            $this->removeOldDirs();

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

    /**
     * Suppression des dossiers plus vieux de 1 jour
     */
    private function removeOldDirs()
    {
        $now = time();
        $dirPath = $this->container->getParameter('paprec_uploaded_files_dir');

        if (is_dir($dirPath)) {
            $files = array_diff(scandir($dirPath), array('.', '..'));
            if ($files && count($files)) {
                foreach ($files as $file) {
                    if (is_dir($dirPath . '/' . $file)) {
                        if ($now - filectime($dirPath . '/' . $file) >= 600) { // si dernière date de modif > 600 sec
                            $this->rmdir_recursive($dirPath . '/' . $file);
                        }
                    } else if (is_file($dirPath . '/' . $file)) {
                        if ($now - filectime($dirPath . '/' . $file) >= 600) { // si dernière date de modif > 600 sec
                            unlink($dirPath . '/' . $file);
                        }
                    }
                }
            }
        }
    }

    private function rmdir_recursive($dir)
    {
        if (is_dir($dir)) {
            $dir_content = scandir($dir);
            if ($dir_content !== FALSE) {
                foreach ($dir_content as $entry) {
                    if (!in_array($entry, array('.', '..'))) {
                        $entry = $dir . '/' . $entry;
                        if (!is_dir($entry)) {
                            unlink($entry);
                        } else {
                            $this->rmdir_recursive($entry);
                        }
                    }
                }
            }
            rmdir($dir);
        }
    }
}
