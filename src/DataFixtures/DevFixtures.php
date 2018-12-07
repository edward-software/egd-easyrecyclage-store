<?php
/**
 * Created by PhpStorm.
 * User: frede
 * Date: 30/11/2018
 * Time: 17:35
 */

namespace DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Paprec\CatalogBundle\Entity\Category;
use Paprec\CatalogBundle\Entity\PostalCode;
use Paprec\CatalogBundle\Entity\PriceListD3E;
use Paprec\CatalogBundle\Entity\PriceListLineD3E;
use Paprec\CatalogBundle\Entity\ProductChantier;
use Paprec\CatalogBundle\Entity\ProductChantierCategory;
use Paprec\CatalogBundle\Entity\ProductD3E;
use Paprec\CatalogBundle\Entity\ProductDI;
use Paprec\CatalogBundle\Entity\ProductDICategory;
use Paprec\CatalogBundle\Repository\CategoryRepository;
use Paprec\CatalogBundle\Repository\PriceListD3ERepository;
use Paprec\CatalogBundle\Repository\ProductChantierRepository;
use Paprec\CatalogBundle\Repository\ProductDIRepository;
use Paprec\CommercialBundle\Entity\Agency;
use Paprec\CommercialBundle\Entity\BusinessLine;
use Paprec\UserBundle\Entity\User;
use Symfony\Component\Console\Output\ConsoleOutput;

class DevFixtures extends Fixture
{

    private $productDIRepository;
    private $productChantierRepository;
    private $categoryRepository;
    private $priceListD3ERepository;

    public function __construct(ProductDIRepository $productDIRepository, ProductChantierRepository $productChantierRepository, CategoryRepository $categoryRepository, PriceListD3ERepository $priceListD3ERepository)
    {
        $this->productDIRepository = $productDIRepository;
        $this->productChantierRepository = $productChantierRepository;
        $this->categoryRepository = $categoryRepository;
        $this->priceListD3ERepository = $priceListD3ERepository;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        /**
         * Création de 6 Product DI
         */
        for ($i = 1; $i <= 6; $i++) {
            $product = new ProductDI();
            $product->setName('Product DI ' . $i);
            $product->setDescription('Description du product DI ' . $i);
            $product->setCapacity('' . mt_rand(10, 200));
            $product->setCapacityUnit('L');
            $product->setDimensions('100x100x100');
            $product->setAvailablePostalCodes('92150, 75015');
            $product->setIsDisplayed(true);

            $manager->persist($product);
        }


        /**
         * Création de 6 Product Chantier
         */
        for ($i = 1; $i <= 6; $i++) {
            $product = new ProductChantier();
            $product->setName('Product Chantier ' . $i);
            $product->setDescription('Description du product Chantier ' . $i);
            $product->setCapacity('' . mt_rand(10, 200));
            $product->setCapacityUnit('L');
            $product->setDimensions('100x100x100');
            $product->setAvailablePostalCodes('92150, 75015');
            $product->setIsDisplayed(true);
            $product->setIsPayableOnline(true);

            $manager->persist($product);
        }


        /**
         * Création des 3 produits D3E
         */
        $productD3E1 = new ProductD3E();
        $productD3E1->setName('Contenant, Collecte et Traitement');
        $productD3E1->setDescription('Mise à disposition des contenants, collecte de ceux-ci et traitement des déchets contenus');
        $productD3E1->setCoefHandling(mt_rand(100, 200));
        $productD3E1->setCoefDestruction(mt_rand(100, 200));
        $productD3E1->setCoefSerialNumberStmt(mt_rand(100, 200));
        $productD3E1->setAvailablePostalCodes('92150, 75015');
        $productD3E1->setPosition(1);
        $productD3E1->setIsDisplayed(true);
        $productD3E1->setIsPayableOnline(true);
        $manager->persist($productD3E1);

        $productD3E2 = new ProductD3E();
        $productD3E2->setName('Collecte et Traitement');
        $productD3E2->setDescription('Collecte de ceux-ci et traitement des déchets contenus');
        $productD3E2->setCoefHandling(mt_rand(100, 200));
        $productD3E2->setCoefDestruction(mt_rand(100, 200));
        $productD3E2->setCoefSerialNumberStmt(mt_rand(100, 200));
        $productD3E2->setAvailablePostalCodes('92150, 75015');
        $productD3E2->setPosition(2);
        $productD3E2->setIsDisplayed(true);
        $productD3E2->setIsPayableOnline(true);
        $manager->persist($productD3E2);

        $productD3E3 = new ProductD3E();
        $productD3E3->setName('Traitement');
        $productD3E3->setDescription('Traitement des déchets uniquement');
        $productD3E3->setCoefHandling(mt_rand(100, 200));
        $productD3E3->setCoefDestruction(mt_rand(100, 200));
        $productD3E3->setCoefSerialNumberStmt(mt_rand(100, 200));
        $productD3E3->setAvailablePostalCodes(m'92150, 75015');
        $productD3E3->setPosition(1);
        $productD3E3->setIsDisplayed(true);
        $productD3E3->setIsPayableOnline(true);
        $manager->persist($productD3E3);


        /**
         * Création de 3 PriceListD3E
         */
        $priceListD3E1 = new PriceListD3E();
        $priceListD3E1->setName('Contenant, Collecte et Traitement');
        $manager->persist($priceListD3E1);
        $priceListD3E2 = new PriceListD3E();
        $priceListD3E2->setName('Collecte et Traitement');
        $manager->persist($priceListD3E2);
        $priceListD3E3 = new PriceListD3E();
        $priceListD3E3->setName('Traitement');
        $manager->persist($priceListD3E3);

        // Get our userManager, you must implement `ContainerAwareInterface`

        // Create our user and set details
        $user = new User();
        $user->setUsername('admin');
        $user->setEmail('email@domain.com');
        $user->setPlainPassword('admin');
        $user->setEnabled(true);
        $user->setRoles(array('ROLE_ADMIN'));
        $manager->persist($user);


        /**
         * Création des Categories
         */
        // Categories DI
        $category1 = new Category();
        $category1->setName('Papiers');
        $category1->setDivision('DI');
        $category1->setPosition(1);
        $category1->setEnabled(true);
        $category1->setDescription('Description Papier');
        $manager->persist($category1);

        $category2 = new Category();
        $category2->setName('Cartons');
        $category2->setDivision('DI');
        $category2->setPosition(2);
        $category2->setEnabled(true);
        $category2->setDescription('Description Cartons');
        $manager->persist($category2);

        $category3 = new Category();
        $category3->setName('Plastiques');
        $category3->setDivision('DI');
        $category3->setPosition(3);
        $category3->setEnabled(true);
        $category3->setDescription('Description Plastiques');
        $manager->persist($category3);

        $category4 = new Category();
        $category4->setName('Palettes');
        $category4->setDivision('DI');
        $category4->setPosition(4);
        $category4->setEnabled(true);
        $category4->setDescription('Description Palettes');
        $manager->persist($category4);

        $category5 = new Category();
        $category5->setName('Encombrants');
        $category5->setDivision('DI');
        $category5->setPosition(5);
        $category5->setEnabled(true);
        $category5->setDescription('Description Encombrants');
        $manager->persist($category5);

        $category6 = new Category();
        $category6->setName('DIB');
        $category6->setDivision('DI');
        $category6->setPosition(6);
        $category6->setEnabled(true);
        $category6->setDescription('Description DIB');
        $manager->persist($category6);

        $category7 = new Category();
        $category7->setName('Bois');
        $category7->setDivision('DI');
        $category7->setPosition(7);
        $category7->setEnabled(true);
        $category7->setDescription('Description Bois');
        $manager->persist($category7);

        $category8 = new Category();
        $category8->setName('Ferrailles');
        $category8->setDivision('DI');
        $category8->setPosition(8);
        $category8->setEnabled(true);
        $category8->setDescription('Description Ferrailles');
        $manager->persist($category8);

        // Catégories Chantier
        $category9 = new Category();
        $category9->setName('DIB');
        $category9->setDivision('CHANTIER');
        $category9->setPosition(1);
        $category9->setEnabled(true);
        $category9->setDescription('Déchets non ');
        $manager->persist($category9);

        $category10 = new Category();
        $category10->setName('Gravats en mélange');
        $category10->setDivision('CHANTIER');
        $category10->setPosition(2);
        $category10->setEnabled(true);
        $category10->setDescription('Description Gravats en mélange');
        $manager->persist($category10);

        $category11 = new Category();
        $category11->setName('Gravats propres inertes');
        $category11->setDivision('CHANTIER');
        $category11->setPosition(3);
        $category11->setEnabled(true);
        $category11->setDescription('Description Gravats propres inertes');
        $manager->persist($category11);

        $category12 = new Category();
        $category12->setName('Bois');
        $category12->setDivision('CHANTIER');
        $category12->setPosition(4);
        $category12->setEnabled(true);
        $category12->setDescription('Description Bois');
        $manager->persist($category12);

        /**
         * Création des agences
         */
        $agency1 = new Agency();
        $agency1->setName("Agence de Suresnes");
        $agency1->setDivisions(array('DI', 'CHANTIER', 'D3E'));
        $agency1->setAddress('2 rue des Bourets');
        $agency1->setPostalCode('92150');
        $agency1->setCity('SURESNES');
        $agency1->setPhone('0156438765');
        $agency1->setLatitude(48.8681378);
        $agency1->setLongitude(2.2271627999999737);
        $agency1->setIsDisplayed(true);
        $manager->persist($agency1);

        $agency2 = new Agency();
        $agency2->setName("Agence de Nantes");
        $agency2->setDivisions(array('DI', 'D3E'));
        $agency2->setAddress('2 place du Commerce');
        $agency2->setPostalCode('44000');
        $agency2->setCity('NANTES');
        $agency2->setPhone('0256438765');
        $agency2->setLatitude(47.2133021);
        $agency2->setLongitude(-1.5582154999999602);
        $agency2->setIsDisplayed(true);
        $manager->persist($agency2);

        $agency3 = new Agency();
        $agency3->setName("Agence de Marseille");
        $agency3->setDivisions(array('DI'));
        $agency3->setAddress('3 impasse de la Cannebiere');
        $agency3->setPostalCode('13000');
        $agency3->setCity('MARSEILLE');
        $agency3->setPhone('0456438765');
        $agency3->setLatitude(43.29727159999999);
        $agency3->setLongitude(5.380121099999997);
        $agency3->setIsDisplayed(true);
        $manager->persist($agency3);

        /**
         * Création des Business Lines
         */
        $businessLine1 = new BusinessLine();
        $businessLine1->setName('BTP');
        $businessLine1->setDivision('CHANTIER');
        $manager->persist($businessLine1);

        $businessLine2 = new BusinessLine();
        $businessLine2->setName('Informatique');
        $businessLine2->setDivision('D3E');
        $manager->persist($businessLine2);

        $businessLine3 = new BusinessLine();
        $businessLine3->setName('Menuiserie');
        $businessLine3->setDivision('DI');
        $manager->persist($businessLine3);

        /**
         * Création des postalCodes
         */
        $postalCode1 = new PostalCode();
        $postalCode1->setDivision('DI');
        $postalCode1->setCode('92*');
        $postalCode1->setRate(mt_rand(100,200));
        $manager->persist($postalCode1);

        $postalCode1 = new PostalCode();
        $postalCode1->setDivision('CHANTIER');
        $postalCode1->setCode('92*');
        $postalCode1->setRate(mt_rand(100,200));
        $manager->persist($postalCode1);

        $postalCode1 = new PostalCode();
        $postalCode1->setDivision('D3E');
        $postalCode1->setCode('92*');
        $postalCode1->setRate(mt_rand(100,200));
        $manager->persist($postalCode1);
        /**
         * On flush une première fois pour créer les référentiels et pouvoir les récupérer ensuite dans des repository->find()
         */
        $manager->flush();

        /**
         *  Création des ProductDICategories
         */
        $productsDI = $this->productDIRepository->findAll();
        $categoriesDI = $this->categoryRepository->findBy(array(
            'division' => 'DI'
        ));
        foreach ($productsDI as $productDI) {
            $cpt = 0;
            foreach ($categoriesDI as $categoryDI) {
                $productDICategory = new ProductDICategory();
                $productDICategory->setPosition($cpt);
                $productDICategory->setUnitPrice(mt_rand(1000, 10000));
                $productDICategory->setProductDI($productDI);
                $productDICategory->setCategory($categoryDI);
                $manager->persist($productDICategory);
                $cpt++;
            }
        }

        /**
         *  Création des ProductChantierCategories
         */
        $productsChantier = $this->productChantierRepository->findAll();
        $categoriesChantier = $this->categoryRepository->findBy(array(
            'division' => 'CHANTIER'
        ));
        foreach ($productsChantier as $productChantier) {
            $cpt = 0;
            foreach ($categoriesChantier as $categoryChantier) {
                $productChantierCategory = new ProductChantierCategory();
                $productChantierCategory->setPosition($cpt);
                $productChantierCategory->setUnitPrice(mt_rand(1000, 10000));
                $productChantierCategory->setProductChantier($productChantier);
                $productChantierCategory->setCategory($categoryChantier);
                $manager->persist($productChantierCategory);
                $cpt++;
            }
        }


        /**
         * Création des PriceListLineD3E
         */
        $priceListD3Es = $this->priceListD3ERepository->findAll();
        foreach ($priceListD3Es as $priceListD3E ){
            $priceListLineD3E1 = new PriceListLineD3E();
            $priceListLineD3E1->setPostalCodes('92150, 75015, 44000');
            $priceListLineD3E1->setMinQuantity(1);
            $priceListLineD3E1->setMaxQuantity(10);
            $priceListLineD3E1->setAgency($agency1);
            $priceListLineD3E1->setPrice(mt_rand(1000, 2000));
            $priceListLineD3E1->setPriceListD3E($priceListD3E);
            $manager->persist($priceListLineD3E1);

            $priceListLineD3E2 = new PriceListLineD3E();
            $priceListLineD3E2->setPostalCodes('92150, 75015, 44000');
            $priceListLineD3E2->setMinQuantity(11);
            $priceListLineD3E2->setMaxQuantity(20);
            $priceListLineD3E2->setAgency($agency1);
            $priceListLineD3E2->setPrice(mt_rand(2000, 3000));
            $priceListLineD3E2->setPriceListD3E($priceListD3E);
            $manager->persist($priceListLineD3E2);

            $priceListLineD3E3 = new PriceListLineD3E();
            $priceListLineD3E3->setPostalCodes('92150, 75015, 44000');
            $priceListLineD3E3->setMinQuantity(21);
            $priceListLineD3E3->setMaxQuantity(100);
            $priceListLineD3E3->setAgency($agency1);
            $priceListLineD3E3->setPrice(mt_rand(3000, 4000));
            $priceListLineD3E3->setPriceListD3E($priceListD3E);
            $manager->persist($priceListLineD3E3);

        }


        /**
         * Liaison entre ProductD3E et PriceListD3E
         */
        $productD3E1->setPriceListD3E($priceListD3E1);
        $productD3E2->setPriceListD3E($priceListD3E2);
        $productD3E3->setPriceListD3E($priceListD3E3);

        $manager->flush();

    }
}