<?php

namespace ZenSide\SonataImageBundle\Entity\Listener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerAware;
use ZenSide\SonataImageBundle\Entity\Image;

class ImageListener extends ContainerAware
{
    const UPLOAD_FOLDER = 'uploads/images/';

    private function moveFile(LifecycleEventArgs $args)
    {
        $image = $args->getObject();

        if ($image instanceof Image) {

            $server_folder = $this->container->get('kernel')->getRootDir().'/../web/'.self::UPLOAD_FOLDER;
            $web_folder = 'http://'.$_SERVER['HTTP_HOST'].$this->container->get('templating.helper.assets')->getUrl(self::UPLOAD_FOLDER);

            // the file property can be empty if the field is not required
            if (null === $image->getFile()) {
                return;
            }

            $imageNewName = uniqid().'.'.pathinfo($image->getFile()->getClientOriginalName(),PATHINFO_EXTENSION);

            $image->setServerFolder($server_folder);
            $image->setWebFolder($web_folder);
            $image->setFilename($imageNewName);

            // move takes the target directory and target filename as params
            $image->getFile()->move($image->getServerFolder(), $image->getFilename());

            // clean up the file property as you won't need it anymore
            $image->setFile(null);
        }
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->moveFile($args);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->moveFile($args);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $image = $args->getObject();
        if ($image instanceof Image) {
            if(file_exists($image->getServerFilePath()) )
            {
                unlink($image->getServerFilePath());
            }
        }
    }


}