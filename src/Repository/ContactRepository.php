<?php

namespace App\Repository;

use App\DataType\EnumColumnType;
use App\Entity\Contact;
use App\Entity\CustomField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\FetchMode;
use http\Exception;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method Contact|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contact|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contact[]    findAll()
 * @method Contact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactRepository extends ServiceEntityRepository
{
    private $connection;
    private $allowedOrders = ['create_at','full_name'];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
        $this->connection = $this->getEntityManager()->getConnection();
    }

    public function getAllWithLatestDoc()
    {
        $stmt = $this->connection->prepare(
            "select distinct on (c.id)
       c.id                           contact_id,
       concat(c.surname, ' ', c.name) full_name,
       d.id                           doc_id,
       d.number                       doc_number,
       d.create_at                    doc_create_at
    from contact c
         left join doc d on d.contact_id = c.id and d.delete_at is null
    where c.delete_at is null
    order by c.id, d.create_at desc"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }


    public function getFiltered(Request $request, $onPage=2)
    {
        $fields = ["id, concat(c.surname, ' ', c.name) full_name, create_at"];
        $wheres = ['c.delete_at is null'];
        $orders = [];
        $params = [];
        $page = 1;

        //add custom fields to select
        $customFields = $this->getEntityManager()->getRepository(CustomField::class)->findAll();
        foreach ($customFields as $customField) {
            $colName = $customField->getName();
            $fields[] = "c.custom_fields->>'$colName' $colName";
            $this->allowedOrders[] = $colName;
        }

        //filters
        if ($request->query->has('filters')) {
            $filters = $request->query->get('filters');
            foreach ($filters as $filterName => $filterVal) {
                switch ($filterName) {
                    case 'full_name':
                        $wheres[] = "(c.name like :full_name_filter or c.surname like :full_name_filter)";
                        $params['full_name_filter'] = "%$filterVal%";
                        break;
                    case 'create_at_from':
                        if (isset($filters['create_at_to'])) {
                            $wheres[] = "c.create_at::date between :create_at_from and :create_at_to";
                            $params['create_at_to'] = $filters['create_at_to'];
                        } else {
                            $wheres[] = 'c.create_at::date >= :create_at_from';
                        }
                        $params['create_at_from'] = $filterVal;
                        break;
                    case 'custom_fields':
                        foreach ($filterVal as $customFieldName => $customFieldVal){
                            $res = array_filter($customFields, function (CustomField $customField) use ($customFieldName){
                                return $customField->getName() === $customFieldName;
                            });
                            if(count($res)){
                                $customField = $res[0];
                                $columnType = $customField->getType();
                                $paramName = "c_{$customFieldName}_filter";
                                switch ($columnType){
                                    case EnumColumnType::TYPE_TEXT:
                                        $wheres[] = "c.custom_fields->>'$customFieldName' like :$paramName";
                                        $params[$paramName] = "%$customFieldVal%";
                                        break;
                                    case EnumColumnType::TYPE_SELECT:
                                        $wheres[] = "c.custom_fields>>'$customFieldName' = :$paramName";
                                        $params[$paramName] = $customFieldVal;
                                        break;
                                    case EnumColumnType::TYPE_DATE:
                                        //@todo add filter for custom date and multiple select
                                        break;
                                }
                            }else
                            {
                                throw new \Exception("Custom field with name $customFieldName doesn't exist");
                            }
                        }
                        break;
                }
            }
        }

        //orders
        if($request->query->has('order')){
            $orderColumns = explode(',', $request->query->get('order'));
            $direction = '';

            foreach ($orderColumns as $orderColumn){
                if($orderColumn[0] === '-'){
                    $orderColumn = ltrim($orderColumn,'-');
                    $direction = 'desc';
                }
                if(!in_array($orderColumn, $this->allowedOrders)){
                    throw new \Exception("Column $orderColumn is not in allowed orders list");
                }
                $orders[] = "$orderColumn $direction";
            }
        }

        //pagination
        if($request->query->has('page')){
            $page = $request->query->get('page');
        }
        $offset = $page * $onPage - $onPage;

        //making query
        $where = implode(' and ', $wheres);
        $select = implode(', ', $fields);
        $order = count($orders) ? " order by ".implode(', ', $orders) : '';

        $sql = "
            select $select
            from contact c
            where $where
            $order
            limit $onPage offset $offset
        ";

        $countSql = "
            select count(*)
            from contact c 
            where $where
        ";
        $qb = $this->connection->prepare($sql);
        $qb->execute($params);

        $countStmt = $this->connection->prepare($countSql);
        $countStmt->execute($params);
        $responseData = [
            'data' => $qb->fetchAll(),
            'total' => $countStmt->fetch()['count'],
            'currentPage' => $page
        ];
        return $responseData;
    }


}
