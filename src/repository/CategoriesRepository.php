<?php

namespace App\Repository;

use App\Entity\Categories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Categories|null find($id, $lockMode = null, $lockVersion = null)
 * @method Categories|null findOneBy(array $criteria, array $orderBy = null)
 * @method Categories[]    findAll()
 * @method Categories[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoriesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categories::class);
    }

    
    // git position by id 
    public function findPositionById($categorieId)
    {
        $result = $this->createQueryBuilder('categ')
            ->andWhere('categ.id = :id')
            ->select('categ.position')
            ->setParameter('id', $categorieId)
            ->getQuery()
            ->getSingleResult()
        ;
        return $result['position'];
    }
    // reset position by old position
    public function resetPosition($oldPosition, $newPosition)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'UPDATE App\Entity\Categories categ
             SET categ.position = :newPos
             WHERE categ.position = :oldPos'

        )->setParameter('newPos', $newPosition)
         ->setParameter('oldPos', $oldPosition);
        
        return $query->getResult();
    }
    // get all positions between oldPosition and newPosition where oldPos < newPos
    public function positionRange($oldPosition, $newPosition, $order = 'asc')
    {
        $result = $this->createQueryBuilder('categ')
            ->andWhere('categ.position >= :oldPos')
            ->andWhere('categ.position <= :newPos')
            ->select('categ.position')
            ->setParameter('oldPos', $oldPosition)
            ->setParameter('newPos', $newPosition)
            ->orderBy('categ.position', $order)
            ->getQuery()
            ->getResult();

        # clean result => set positions in array as intejers
        foreach ($result as $key => $value){
            $result[$key] = $value['position'];
        }
        return $result;
    }

    // get the position above the curent position
    public function getPosByPos($currentPos, $direction)
    {
        if ($direction == 'up'){
            $result = $this->createQueryBuilder('categ')
                ->andWhere('categ.position < :Pos')
                ->select('MAX(categ.position) AS position')
                ->setParameter('Pos', $currentPos)
                ->getQuery()
                ->getSingleResult();
        }
        else{
            $result = $this->createQueryBuilder('categ')
                ->andWhere('categ.position > :Pos')
                ->select('MIN(categ.position) AS position')
                ->setParameter('Pos', $currentPos)
                ->getQuery()
                ->getSingleResult();
        }

        return $result['position'];
    }

    // get the position above the curent position
    public function getFirstPos()
    {
            $result = $this->createQueryBuilder('categ')
                ->select('MIN(categ.position) AS position')
                ->getQuery()
                ->getSingleResult();

        return intval($result['position']);
    }
}