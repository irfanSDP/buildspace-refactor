<?php
/*
 * This is a simple short-hand wrapper for Doctrine_Query. It provides
 * a shorter class name and a few additional functions.
 */
class DoctrineQuery extends Doctrine_Query
{
    /**
     * Returns a DoctrineQuery object to get started
     *
     * @return DoctrineQuery
     */
    public static function create($conn = null, $class = null)
    {
        return new DoctrineQuery($conn);
    }

    /**
     * This function will wrap the current dql where statement
     * in parenthesis. This allows more complex dql statements
     * It can be called multiple times during the creation of the dql
     * where clause.
     *
     * @return $this
     */
    public function whereParenWrap()
    {
        $where = $this->_dqlParts['where'];
        if (count($where) > 0)
        {
            array_unshift($where, '(');
            array_push($where, ')');
            $this->_dqlParts['where'] = $where;
        }
        return $this;
    }

    /**
     * Create and andWhere if the where parameter is not empty
     *
     * @param string $where where string
     * @param parameters $params
     *
     * @return DQ this object
     */
    public function andWhereIf($where, $params = array())
    {
        return empty($where) ? $this : $this->andWhere($where, $params);
    }

    /**
     * Create and orWhere if the where parameter is not empty
     *
     * @param string $where where string
     * @param parameters $params
     *
     * @return DQ this object
     */
    public function orWhereIf($where, $params = array())
    {
        return empty($where) ? $this : $this->orWhere($where, $params);
    }

}
?>
