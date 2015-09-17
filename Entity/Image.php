<?php

namespace ZenSide\SonataImageBundle\Entity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\Mapping as ORM;

/**
 * Image
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="ZenSide\QQQBundle\Entity\Repository\ImageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Image
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=255)
     */
    private $filename;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    private $updated;
    /**
     * @var string
     *
     * @ORM\Column(name="serverFolder", type="string", length=255)
     */
    private $serverFolder;
    /**
     * @var string
     *
     * @ORM\Column(name="webFolder", type="string", length=255)
     */
    private $webFolder;

    public function getServerFilePath(){
        return $this->getServerFolder().$this->getFilename();
    }

    /**
     * @JMS\VirtualProperty
     */
    public function getUrl(){
        return $this->getWebFilePath();
    }
    public function getWebFilePath(){
        return $this->getWebFolder().$this->getFilename();
    }

    /**
     * @return string
     */
    public function getServerFolder()
    {
        return $this->serverFolder;
    }

    /**
     * @param string $serverFolder
     */
    public function setServerFolder($serverFolder)
    {
        $this->serverFolder = $serverFolder;
    }

    /**
     * @return string
     */
    public function getWebFolder()
    {
        return $this->webFolder;
    }

    /**
     * @param string $webFolder
     */
    public function setWebFolder($webFolder)
    {
        $this->webFolder = $webFolder;
    }

    /**
     * @JMS\VirtualProperty
     */
    public function getSize()
    {
        return getimagesize($this->getServerFilePath());
    }


    /**
     * Unmapped property to handle file uploads
     */
    private $file;

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Updates the hash value to force the preUpdate and postUpdate events to fire
     */
    public function refreshUpdated() {
        $this->setUpdated(new \DateTime("now"));
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set filename
     *
     * @param string $filename
     * @return Image
     */
    public function setFilename($filename)
    {

        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Image
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }
    public function __toString() {
        return '<img src="'.$this->path.'">';
    }

}
