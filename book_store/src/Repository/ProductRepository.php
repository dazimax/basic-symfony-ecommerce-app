<?php
/**
 * ProductRepository Repository
 *
 * @file     Books Store
 * @category 99x
 * @package  99x_books_store
 * @author   Dasitha Abeysinghe <dazimax@gmail.com>
 * @access   public
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Category;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Get Products By CategoryId
     * 
     * @param int $categoryId
     * 
     * @return Product[] Returns an array of Product objects
     * 
     * @access public 
     * 
     */
    public function getProductsByCategoryId(int $categoryId)
    {
        $productCollection = $this->createQueryBuilder('p')
            ->andWhere('p.category_id = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->getQuery()
            ->getResult()
        ;
        return $productCollection;
    }

    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
