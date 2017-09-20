<?php

namespace Delz\Phalcon\Pagination;

use Delz\Paginator\Contract\IPagerAdapter;
use Phalcon\Mvc\Model\Query\BuilderInterface;

/**
 * QueryBuilder实现的分页器
 *
 * @package Delz\Phalcon\Pagination
 */
class QueryBuilder implements IPagerAdapter
{
    /**
     * @var BuilderInterface
     */
    private $builder;

    /**
     * @var BuilderInterface
     */
    private $totalBuilder;

    public function __construct(BuilderInterface $builder)
    {
        $this->builder = $builder;
        $this->totalBuilder = clone $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults($offset, $limit)
    {
        $this->builder->limit($limit, $offset);
        $query = $this->builder->getQuery();
        return $query->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $this->totalBuilder->columns("COUNT(*) [rowcount]");
        //如果存在group by，修改COUNT()参数
        $groups = $this->totalBuilder->getGroupBy();
        if (!empty($groups)) {
            if (is_array($groups)) {
                $groupColumn = implode(',', $groups);
            } else {
                $groupColumn = $groups;
            }
            $this->totalBuilder->groupBy(null)->columns(["COUNT(DISTINCT " . $groupColumn . ") AS rowcount"]);
        }
        //删除排序
        $this->totalBuilder->orderBy(null);
        $query = $this->totalBuilder->getQuery();
        $row = $query->execute()->getFirst();

        return $row ? $row->rowcount : 0;
    }
}