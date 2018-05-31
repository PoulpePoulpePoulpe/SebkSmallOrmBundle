<?php

namespace Sebk\SmallOrmBundle\Validator;

use Sebk\SmallOrmBundle\Factory\Dao;
use Sebk\SmallOrmBundle\Dao\Model;

/**
 * Class AbstractValidator
 * @package Sebk\SmallOrmBundle\Validator
 */
abstract class AbstractValidator
{
    protected $daoFactory;
    protected $model;
    protected $message;

    /**
     * AbstractValidator constructor.
     *
     * @param Dao $daoFactory
     * @param Model $model
     */
    public function __construct(Dao $daoFactory, Model $model)
    {
        $this->model      = $model;
        $this->daoFactory = $daoFactory;
    }

    /**
     *
     * @param type $property
     * @param type $table
     * @param type $idTable
     * @return type
     */
    /*public function testRelation($property, $table, $idTable)
    {
        $daoCible   = $this->factory->getDao($table);
        $whereArray = array(
            array(
                "modelFieldName" => $idTable,
                "operator" => "=",
                "valeur" => $this->model->$property,
            ),
        );
        $result     = $daoCible->select($whereArray);

        return count($result) == 1;
    }*/

    /**
     * Validation abstract
     */
    abstract public function validate();

    /**
     * Test if field is empty
     *
     * @param string $field
     * @return boolean
     *
     * @throws \Sebk\SmallOrmBundle\Dao\ModelException
     */
    public function testNonEmpty($field)
    {
        $method = "get".$field;
        if ($this->model->$method() !== null && trim($this->model->$method()) != "") {
            return true;
        }

        return false;
    }

    /**
     * Get errors message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Test if field is unique
     *
     * @param string $field
     *
     * @return string
     *
     * @throws \Sebk\SmallOrmBundle\Dao\ModelException
     * @throws \Sebk\SmallOrmBundle\Factory\ConfigurationException
     * @throws \Sebk\SmallOrmBundle\Factory\DaoNotFoundException
     */
    public function testUnique($field)
    {
        $dao      = $this->daoFactory->get($this->model->getBundle(),
            $this->model->getModelName());
        $creation = !$this->model->fromDb;

        $query  = $dao->createQueryBuilder("uniqueTable");
        $where  = $query->where();
        $method = "get".$field;

        if ($creation) {
            $result = $dao->findBy(array($field => $this->model->$method()));
        } else {
            $first = true;
            foreach ($this->model->getPrimaryKeys() as $key => $value) {
                if ($first) {
                    $where->firstCondition($query->getFieldForCondition($key),
                        "<>", ":".$key."Primary");
                    $query->setParameter($key."Primary", $value);
                } else {
                    $where->andCondition($query->getFieldForCondition($key),
                        "<>", ":".$key."Primary");
                    $query->setParameter($key."Primary", $value);
                }
            }

            $where->andCondition($query->getFieldForCondition($field), "=",
                ":".$field);
            $query->setParameter($field, $this->model->$method());

            $result = $dao->getResult($query);
        }

        return count($result) == 0;
    }

    /**
     * Test if field is an integer
     *
     * @param $field
     *
     * @return bool
     *
     * @throws \Sebk\SmallOrmBundle\Dao\ModelException
     */
    public function testInteger($field)
    {
        $method = "get".$field;
        $value = $this->model->$method();
        $numbers = str_split("0123456789");
        for($i = 0; $i < strlen($value); $i++) {
            if(!in_array(substr($value, $i, 1), $numbers)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Test field with filter_var
     *
     * @param mixed     $field      Can be a string (for filter_var) or an array (for filter_var_array)
     * @param mixed     $filter     Can be a int (for filter_var) or an array (for filter_var_array)
     * @param mixed     $options    For flags.
     *
     * @return bool
     *
     * @throws \Sebk\SmallOrmBundle\Dao\ModelException
     */
    public function testFilterVar($field, $filter, $options)
    {
        $method = "get".$field;
        $value = $this->model->$method();

        if (is_array($value)) {
            $options = !is_bool($options)? true : $options;
            if (filter_var_array($value, $filter, $options) !== false) {
                return true;
            }
        } else {
            if (filter_var($value, $filter, $options) !== false) {
                return true;
            }
        }

        return false;
    }
}