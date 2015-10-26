<?php

namespace ZenSide\SonataImageBundle\Entity\Listener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerAware;
use ZenSide\SonataImageBundle\Entity\Image;

class ImageListener extends ContainerAware
{
    const UPLOAD_FOLDER = 'uploads/images/',
        MAX_WIDTH = 1500,
        MAX_HEIGHT = 1000;

    private function moveFile(LifecycleEventArgs $args)
    {
        $image = $args->getObject();

        if ($image instanceof Image) {

            $server_folder = $this->container->get('kernel')->getRootDir() . '/../web/' . self::UPLOAD_FOLDER;

            $web_folder = '';
            if (isset($_SERVER['HTTP_HOST'])) {
                $web_folder = 'http://' . $_SERVER['HTTP_HOST'];
            }
            $web_folder .= $this->container->get('templating.helper.assets')->getUrl(self::UPLOAD_FOLDER);

            // the file property can be empty if the field is not required
            if (null === $image->getFile()) {
                return;
            }

            $imageNewName = uniqid() . '.' . pathinfo($image->getFile()->getClientOriginalName(), PATHINFO_EXTENSION);

            $image->setServerFolder($server_folder);
            $image->setWebFolder($web_folder);
            $image->setFilename($imageNewName);

            // move takes the target directory and target filename as params
            $image->getFile()->move($image->getServerFolder(), $image->getFilename());

            list($width_orig, $height_orig) = $image->getSize();

            if($width_orig != 0 && $height_orig!=0)
            {
                // Calcul des nouvelles dimensions

                if ($this->container->hasParameter('imageresize.maxwidth')) {
                    $width = $this->container->getParameter('imageresize.maxwidth');
                } else {
                    $width = self::MAX_WIDTH;
                }
                if ($this->container->hasParameter('imageresize.maxheight')) {
                    $height = $this->container->getParameter('imageresize.maxheight');
                } else {
                    $height = self::MAX_HEIGHT;
                }


                $ratio_orig = $width_orig / $height_orig;

                if ($width / $height > $ratio_orig) {
                    $width = $height * $ratio_orig;
                } else {
                    $height = $width / $ratio_orig;
                }
                // Redimensionnement

                $extension = strtoupper($image->getExtension());
                switch ($extension) {
                    case 'PNG':
                        $imageOriginale = imagecreatefrompng($image->getServerFilePath());
                        break;
                    case 'JPEG':
                    case 'JPG':
                        $imageOriginale = imagecreatefromjpeg($image->getServerFilePath());
                        break;
                    case 'GIF':
                        $imageOriginale = imagecreatefromgif($image->getServerFilePath());
                        break;
                }

                switch ($extension) {
                    case 'PNG':
                    case 'JPEG':
                    case 'JPG':
                    case 'GIF':
                        $image_p = imagecreatetruecolor($width, $height);
                        imagealphablending($image_p, false);
                        imagesavealpha($image_p, true);
                        imagecopyresampled($image_p, $imageOriginale, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
                        $mediumFileName = $image->getServerFolder() . $image->getFileNameWithoutExtension() . '_medium.' . $image->getExtension();
                        break;
                }

                switch ($extension) {
                    case 'PNG':
                        imagepng($image_p, $mediumFileName);
                        break;
                    case 'JPEG':
                    case 'JPG':
                        imagejpeg($image_p, $mediumFileName);
                        break;
                    case 'GIF':
                        imagegif($image_p, $mediumFileName);
                        break;
                }
            }


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
            if (file_exists($image->getServerFilePath())) {
//                unlink($image->getServerFilePath());
            }
        }
    }


}
