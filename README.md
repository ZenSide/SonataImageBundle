ZenSide Sonata Image Bundle
==========================

Allow to easyly add Image property to an entity to be included in Sonata Forms.

Configuration
------------
After downloaded and add to AppKernel the bundle, update your database to add Image entity

    php app/console doctrine:schema:update --force

Then you can add relations to Image type in your entities

    class Article {
        /**
         * @ORM\OneToOne(targetEntity="ZenSide\SonataImageBundle\Entity\Image", cascade={"persist"})
         * @ORM\JoinColumn(nullable=true, onDelete="set null")
         */
        private $image;
    }

If you want to directly use it in a SonataAdmin class, just use the field with type 'sonata_type_admin' or 'sonata_type_model'
 
    $form->add('logo', 'sonata_type_admin');
    
Important : if you use 'sonata_type_admin' to directly include input type file in your form, you have to add the following preUpdate and prePersist listeners in your Admin class.

Just copy the following lines in your Admin class :
    
    public function prePersist($subject)
    {
        ImageAdmin::manageEmbeddedImageAdmins($this, $subject);
    }
    public function preUpdate($subject)
    {
        ImageAdmin::manageEmbeddedImageAdmins($this, $subject);
    }
 
