<?php
/**
 * Khaus Framework (https://github.com/niucode/khaus)
 *
 * @link https://github.com/niucode/khaus for the canonical source repository
 * @copyright Copyright (c) 2013 rhyudek1. (https://github.com/niucode/khaus)
 * @license https://github.com/niucode/khaus New BSD License
 *
 * @category    Khaus
 * @package     Khaus_Pattern
 * @version     9:20130402
 */

class Khaus_Pattern_ActiveRecord
{
    private $_storeData;
    private $_db;
    private $_tableName;
    private $_innerJoins;
    private $_outerJoins;
    private $_filter;
    private $_order;
    private $_groupBy;
    private $_limit;
    private $_insertType = '';
    private $_lastStatement;
    
    /**
     * Patron de diseño ActiveRecord
     * 
     * Acepta como parametro el nombre de la tabla a usar, se pueden agregar mas
     * tablas luego utilizando los metodos *Join()
     * 
     * @example
     * $personal = new Khaus_Pattern_ActiveRecord('personal');
     * 
     * @access public
     * @param string $tableName nombre de la tabla
     * @param string $_ segunda tabla
     * @throws Khaus_Pattern_Exception si no se informo la tabla
     */
    public function __construct($tableName)
    {
        $this->_tableName = $tableName;
        $this->_db = Khaus_Db_Connection::getInstance();
        $this->_innerJoins = array();
        $this->_storeData = array();
        $this->_filter = array();
    }
    
    /**
     * Agrega un nuevo filtro utilizando formato
     *
     * Se entrega como parametro lo que va en la seccion WHERE 
     * de la consulta SQL, se pueden usar multiples parametros
     * utilizando la notacion de texto formateado @see printf
     * @example 
     * # filtrar el contenido de la tabla
     * $activeRecord->filter("name = %s AND age = %d", 'hidek1', 26);
     *
     * @access public
     * @param string $filter 
     * @return Khaus_Pattern_ActiveRecord 
     */
    public function filter($filter, $_ = null)
    {
        $data = Khaus_Helper_Array::arrayFlatten(func_get_args());
        $data = array_slice($data, 1);
        if (count($data) > 0) {
            $newData = array();
            foreach ($data as $key => $value) {
                if (is_numeric($value)) {
                    $newData[$key] = $value;
                } else {
                    $newData[$key] = $this->_db->quote((string) $value);
                }
            }
            $filter = preg_replace('/("|\')%([0-9]+?)\$s("|\')/i', '%$2$s', $filter);
            $filter = str_replace(array('"%s"', "'%s'"), '%s', $filter);
            $filter = vsprintf($filter, $newData);
        }
        if (!in_array($filter, $this->_filter)) {
            $this->_filter[] = $filter;
        }
        return $this;
    }

    /**
     * Agrega un nuevo filtro del tipo IN
     *
     * Se entrega como primer parametro el nombre de la columna 
     * a la cual se le realizara el filtrado, y como segundo parametro
     * un arreglo con los datos que iran dentro de los parentesis de IN
     * @example 
     * # filtrar el contenido de la tabla
     * $array = array(1, 2, 3);
     * $activeRecord->filterIN('estado', $array); // WHERE estado IN (1, 2, 3);
     * 
     * @param  string $column nombre de la columna
     * @param  array $groupParams arreglo unidimensional con los datos del IN
     * @return Khaus_Pattern_ActiveRecord 
     */
    public function filterIN($column, array $groupParams)
    {
        foreach ($groupParams as $key => $value) {
            if (!is_numeric($value)) {
                $groupParams[$key] = $this->_db->quote((string) $value);
            }
        }
        $filter = sprintf('%s IN (%s)', $column, implode(', ', $groupParams));
        if (!in_array($filter, $this->_filter)) {
            $this->_filter[] = $filter;
        }
        return $this;
    }
    
    /**
     * Funcion deprecated, utilizar filter()
     * 
     * @deprecated utilizar filter()
     * @access public
     * @param string $filter 
     * @return Khaus_Pattern_ActiveRecord 
     */
    public function ffilter($filter, $_ = null)
    {
        call_user_func_array(array($this, "filter"), func_get_args());
        return $this;
    }
    
    /**
     * Elimina los filtros generados
     *
     * Borra todos los filtros generados con el metodo $this->filter()
     * @example 
     * # eliminar filtros creados
     * $activeRecord->filter("name = '%s' AND age = %d", 'hidek1', 26);
     * $activeRecord->clearFilter();
     * 
     * @access public
     * @return Khaus_Pattern_ActiveRecord 
     */
    public function clearFilter()
    {
        $this->_filter = array();
        return $this;
    }
    
    /**
     * Ordena la tabla
     *
     * Ordena los resultado de forma descendiente, en los argumentos
     * se entrega el nombre de la o las tablas.
     * @example
     * # ordena el resultado de la tabla
     * $activeRecord->ordesc('columna1', 'columna2');
     * 
     * @access public
     * @param string $columnName Nombre de la columna
     * @param string [optional]$_ Nombre de la columna
     * @return Khaus_Pattern_ActiveRecord 
     */
    public function ordesc($columnName, $_ = null)
    {
        $columns = Khaus_Helper_Array::arrayFlatten(func_get_args());
        $columns = implode(', ', $columns);
        $this->_order = $columns . ' DESC';
        return $this;
    }
    
    /**
     * Ordena la tabla
     *
     * Ordena los resultado de forma ascendiente, en los argumentos
     * se entrega el nombre de la o las tablas.
     * @example
     * # ordena el resultado de la tabla
     * $activeRecord->ordasc('columna1', 'columna2');
     * 
     * @access public
     * @param string $columnName Nombre de la columna
     * @param string [optional]$_ Nombre de la columna
     * @return Khaus_Pattern_ActiveRecord 
     */
    public function ordasc($columnName, $_ = null)
    {
        $columns = Khaus_Helper_Array::arrayFlatten(func_get_args());
        $columns = implode(', ', $columns);
        $this->_order = $columns . ' ASC';
        return $this;
    }
    
    /**
     * Agrupa los resultados de la consulta
     *
     * Se utiliza para generar un agrupamiento de los resultados por un campo
     * especifico, por lo general cuando se realiza alguna operacion como 
     * COUNT() o MAX() dentro del SELECT
     * 
     * @example
     * # obtiene un resumen de ventas por mes
     * $activeRecord->groupBy('mes');
     * $activeRecord->get('mes', 'SUM(total_venta)');
     * 
     * @access public
     * @param string $columnName Nombre de la columna
     * @param string [optional]$_ Nombre de la columna
     * @return Khaus_Pattern_ActiveRecord 
     */
    public function groupBy($columnName, $_ = null)
    {
        $columns = Khaus_Helper_Array::arrayFlatten(func_get_args());
        $columns = implode(', ', $columns);
        $this->_groupBy = $columns;
        return $this;
    }
    
    /**
     * Limita la cantidad de resultados
     *
     * Ordena una cierta cantidad de resultados, en los argumentos
     * se puede entregar solo el primero para limitar la cantidad
     * o los dos para poner empezar de una cierta posicion los resultados
     * @example
     * 
     * # retorna del 5 al 10
     * $activeRecord->limit(5, 5);
     * 
     * # retorna los primeros 7 resultados
     * $activeRecord->limit(7);
     * 
     * @access public
     * @param string $limit1 Limite de resultados o posicion de inicio de resultados
     * @param string [optional]$limit2 cantidad de resultados
     * @return Khaus_Pattern_ActiveRecord 
     */
    public function limit($limit1, $limit2 = null)
    {
        $this->_limit = $limit1;
        if ($limit2 != null) {
            $this->_limit .= ', ' . $limit2;
        }
        return $this;
    }
    
    /**
     * Enlaza multiples tablas con INNER JOIN
     *
     * Con la instruccion de SQL, INNER JOIN enlaza dos o mas tablas
     * entregando un array asociativo como parametro, en este array se debe
     * entregar como llave el nombre de la segunda tabla y como valor
     * la igualdad de las columnas
     * @example
     * # obtener datos de dos tablas enlazadas
     * $this->activeRecord('tabla1')
     *      ->innerJoin(array('tabla2' => 'tabla1.primaryKey = tabla2.foreignKey'))
     *      ->get('tabla1.name', 'table2.comments');
     * 
     * @access public
     * @param  array  $newTables [description]
     * @return Khaus_Pattern_ActiveRecord
     */
    public function innerJoin(array $newTables)
    {
        foreach ($newTables as $tableName => $onCondition) {
            $this->_innerJoins[$tableName] = (string) $onCondition;
        }
        return $this;
    }

    /**
     * Enlaza multiples tablas con OUTER JOIN
     *
     * Con la instruccion de SQL, OUTER JOIN enlaza dos o mas tablas
     * entregando la dirección del JOIN y un array asociativo como parametros, 
     * en este array se debe entregar como llave el nombre de la segunda tabla y como valor
     * la igualdad de las columnas
     * @example
     * # obtener datos de dos tablas enlazadas
     * $this->activeRecord('tabla1')
     *      ->outerJoin('left', array('tabla2' => 'tabla1.primaryKey = tabla2.foreignKey'))
     *      ->get('tabla1.name', 'table2.comments');
     * 
     * @access public
     * @param  string $direction direccion del outer join
     * @param  array  $newTables [description]
     * @return Khaus_Pattern_ActiveRecord
     */
    public function outerJoin($direction, array $newTables)
    {
        $direction = strtoupper($direction);
        if ($direction == 'LEFT' || $direction == 'RIGHT') {
            foreach ($newTables as $tableName => $onCondition) {
                $outer = new stdClass;
                $outer->tableName = (string) $tableName;
                $outer->onCondition = (string) $onCondition;
                $outer->direction = (string) $direction;
                $this->_outerJoins[] = $outer;
            }
        } else {
            throw new Khaus_Pattern_Exception('Direcci&oacute;n del JOIN inv&aacute;lida');
        }
        return $this;
    }
    
    /**
     * Obtiene datos de la tabla en una array asociativo
     * 
     * Ingresando como parametros los nombres de las columnas
     * se obtiene un array con los datos solicitados
     * los errores seran procesados por PDO::PDOException
     * 
     * @example 
     * # obtener los datos del primer registro
     * $activeRecord->filter('id = 1');
     * $activeRecord->get('nombre', 'ciudad');
     * 
     * @access public
     * @param mixed $columnFilter Nombre de la columna
     * @param mixed $_
     * @return PDO::fetchAll(); 
     */
    public function get($columnFilter = null, $_ = null)
    {
        $columnFilter = Khaus_Helper_Array::arrayFlatten(func_get_args());
        $statement = $this->_constructSelectQuery($columnFilter);
        $query = $this->_db->query($statement);
        $this->_lastStatement = $query;
        return $query->fetchAll();
    }
    
    /**
     * Obtiene los registros de una columna en un array numerico
     * 
     * @example
     * $nombres = $activeRecord->getColumn('nombre');
     * echo $nombres[0]; // muestra el primer registro
     * 
     * @param string $columnName nombre de la columna
     * @return array
     */
    public function getColumn($columnName)
    {
        return Khaus_Helper_Array::arrayFlatten($this->get($columnName));
    }
    
    /**
     * Obtiene un string con la consulta SQL
     * 
     * Para efectos de debugueo en las consultas, verificacion de errores
     * obtiene la consulta SELECT generada por la clase
     * 
     * @param mixed $columnFilter nombres de las columnas
     * @param mixed $_
     * @return string
     */
    public function getQuerySelect($columnFilter = null, $_ = null)
    {
        $columnFilter = Khaus_Helper_Array::arrayFlatten(func_get_args());
        return $this->_constructSelectQuery($columnFilter);
    }
    
    /**
     * Obtiene la cantidad de resultados que arroja una consulta
     * 
     * Se obtiene un dato tipo integer de la cantidad de resultados
     * que la consulta generada retorna
     * Los resultados omiten si se ha entregado un limite a la consulta
     * los errores seran procesados por PDO::PDOException
     * 
     * @example 
     * # obtener la cantidad de usuarios mayores de edad
     * $activeRecord->filter('edad > 17');
     * echo $activeRecord->count();
     * 
     * @access public
     * @return PDO::fetchAll(); 
     */
    public function count()
    {
        $statement = $this->_constructSelectQuery(array('count(1) as count'));
        $query = $this->_db->query($statement);
        return $query->fetch()->count;
    }
    
    /**
     * Obtiene datos de la tabla en un array numerico
     * 
     * Ingresando como parametros los nombres de las columnas
     * se obtiene un array con los datos solicitados
     * los errores seran procesados por PDO::PDOException
     * 
     * @example 
     * # obtener los datos del primer registro
     * $activeRecord->filter('id = 1');
     * $activeRecord->nget('nombre', 'ciudad');
     * 
     * @access public
     * @param mixed $columnFilter Nombre de la columna
     * @param mixed $_
     * @return array; 
     */
    public function nget($columnFilter = null, $_ = null)
    {
        $results = $this->get(func_get_args());
        $array = array();
        $counter = 0;
        foreach ($results as $elements) {
            foreach ($elements as $value) {
                $array[$counter][] = $value;
            }
            $counter++;
        }
        return $array;
    }
    
    /**
     * Obtiene el primer resultado de la tabla
     * 
     * Ingresando como parametros los nombres de las columnas
     * se obtiene un array con el datos solicitado
     * los errores seran procesados por PDO::PDOException
     * 
     * @example 
     * # obtener el primer usuario de chile
     * $activeRecord->filter("pais = 'chile'");
     * echo $activeRecord->getFirst('nombre', 'apellido')->nombre;
     * 
     * @access public
     * @param mixed $columnFilter Nombre de la columna
     * @param mixed $_
     * @return PDO::fetchAll(); 
     */
    public function getFirst($columnFilter = null, $_ = null)
    {
        $elements = $this->get(func_get_args());
        foreach ($elements as $key => $value) {
            return $value;
        }
    }

    /**
     * Obtiene el primer resultado de la tabla en un array numerico
     * 
     * Ingresando como parametros los nombres de las columnas
     * se obtiene un array con los datos solicitados
     * los errores seran procesados por PDO::PDOException
     * 
     * @example 
     * # obtener el primer usuario de chile
     * $activeRecord->filter("pais = 'chile'");
     * $datos = $activeRecord->ngetFirst('nombre', 'ciudad');
     * print $datos[0];
     * 
     * @access public
     * @param mixed $columnFilter Nombre de la columna
     * @param mixed $_
     * @return array; 
     */
    public function ngetFirst($columnFilter = null, $_ = null)
    {
        $results = $this->getFirst(func_get_args());
        return array_values((array) $results);
    }
    
    /**
     * Actualiza los registros
     * 
     * Si se han efectuado cambios los actualiza en la 
     * base de datos
     * @example
     * # cambiar el nombre del primer registro a hidek1
     * $activeRecord->filter('id = 1');
     * $activeRecord->nombre = 'hidek1';
     * $activeRecord->update();
     * 
     * @access public
     * @return boolean
     */
    public function update()
    {
        if (!empty($this->_storeData)) {
            $store = array();
            $statement = "UPDATE $this->_tableName SET ";
            foreach ($this->_storeData as $key => $value) {
                $store[] = $key . ' = :' . $key;
            }
            $statement .= implode(', ', $store);
            if (!empty($this->_filter)) {
                $filters = implode(' AND ', $this->_filter);
                $statement .= " WHERE $filters";
            }
            $query = $this->_db->prepare($statement);
            foreach ($this->_storeData as $key => $value) {
                $parameter = ':' . $key;
                $query->bindValue($parameter, $value);
            }
            $this->_lastStatement = $query;
            return $query->execute();
        }
    }
    
    /**
     * Ingresa nuevos registros
     * 
     * Agrega nueva informacion a la base de datos
     * deben especificarse anteriormente los datos
     * en caso contrario no se efectuara ningun cambio
     * @example
     * # crear un nuevo registro para hidek1 de tokyo
     * $activeRecord->nombre = 'hidek1';
     * $activeRecord->ciudad = 'tokyo';
     * $activeRecord->insert();
     * 
     * @access public
     * @return boolean
     */
    public function insert()
    {
        if (!empty($this->_storeData)) {
            $storeKeys = array_keys($this->_storeData);
            $columns = implode(', ', $storeKeys);
            $columnsParam = implode(', :', $storeKeys);
            $statement = "INSERT $this->_insertType INTO $this->_tableName "
                       . "($columns) VALUES (:$columnsParam)";
            $query = $this->_db->prepare($statement);
            foreach ($this->_storeData as $key => $value) {
                $parameter = ':' . $key;
                $query->bindValue($parameter, $value);
            }
            $this->_lastStatement = $query;
            return $query->execute();
        }
        return false;
    }
    
    /**
     * Ingresa nuevos registros solo si no existen previamente PK
     * 
     * Agrega nueva informacion a la base de datos ignorando si estas ya existen
     * deben especificarse anteriormente los datos
     * en caso contrario no se efectuara ningun cambio
     * @example
     * # crear un nuevo registro para hidek1 de tokyo
     * $activeRecord->nombre = 'hidek1';
     * $activeRecord->ciudad = 'tokyo';
     * $activeRecord->insertIgnore();
     * 
     * @access public
     * @return boolean
     */
    public function insertIgnore()
    {
        $this->_insertType = 'IGNORE';
        $this->insert();
        $this->_insertType = '';
    }
    
    /**
     * Elimina registros
     * 
     * Borra los registros, atencion con este metodo.
     * ya que si no se entrega anteriormente un filtro
     * se eliminaran todos los datos de la tabla.
     * @example 
     * # eliminar todos los habitantes de tokyo
     * $activeRecord->filter("ciudad = 'tokyo'");
     * $activeRecord->delete();
     * 
     * @access public
     * @return boolean
     */
    public function delete()
    {
        $statement = "DELETE FROM $this->_tableName ";
        if (!empty($this->_filter)) {
            $filters = implode(' AND ', $this->_filter);
            $statement .= " WHERE $filters";
        }
        $query = $this->_db->prepare($statement);
        $this->_lastStatement = $query;
        return $query->execute();
    }
    
    /**
     * Devuelve el numero de filas afectadas por la ultima sentencia SQL
     * 
     * Si la ultima sentencia SQL ejecutada fue una sentencia SELECT, 
     * algunas bases de datos podrían devolver el número de filas devuelto 
     * por dicha sentencia. Sin embargo, este comportamiento no está garantizado 
     * para todas las bases de datos y no debería confiarse en el para 
     * aplicaciones portables.
     * 
     * @example 
     * # cuantos habitantes de tokio fueron eliminados?
     * $activeRecord->filter("ciudad = 'tokyo'");
     * $activeRecord->delete();
     * echo 'en tokio fueron eliminados ' . $activeRecord->affected() . ' habitantes';
     * 
     * @access public
     * @return integer
     */
    public function affected()
    {
        if (!empty($this->_lastStatement)) {
            return $this->_lastStatement->rowCount();
        } else {
            return 0;
        }
    }
    
    /**
     * Obtiene el mayor de los registros por columna
     * 
     * Como parametro se entrega el nombre de una columna
     * Se buscara el valor mayor, ya sea numerico, alfanumerico
     * u otro. generalmente usado con columnas de llave primaria
     * @example 
     * # obtener el id mas alto
     * $activeRecord->max("id");
     * 
     * @access public
     * @return PDO::fetch(); 
     */
    public function max($colName)
    {
        $statement = "SELECT max($colName) as max ";
        $statement .= "FROM $this->_tableName ";
        if (!empty($this->_filter)) {
            $statement .= "WHERE " . implode(' AND ', $this->_filter) . " ";
        }
        return $this->_db->query($statement)->fetch()->max;
    }
    
    public function __set($name, $value)
    {
        $this->_storeData[$name] = $value;
    }
    
    public function __get($name)
    {
        if (isset($this->_storeData[$name])) {
            return $this->_storeData[$name];
        } else {
            $data = $this->get($name);
            $data = Khaus_Helper_Array::arrayFlatten($data);
            if (count($data) == 1) {
                $data = $data[0];
            }
            return $data;
        }
    }
    
    private function _constructSelectQuery(array $columnFilter = array())
    {
        $columns = empty($columnFilter) ? '*' : implode(', ', $columnFilter);
        $statement  = "SELECT $columns ";
        $statement .= "FROM $this->_tableName ";
        if (!empty($this->_innerJoins)) {
            foreach ($this->_innerJoins as $tableName => $onCondition) {
                $statement .= "INNER JOIN $tableName ";
                $statement .= "ON $onCondition ";
            }
        }
        if (!empty($this->_outerJoins)) {
            foreach ($this->_outerJoins as $outerObject) {
                $statement .= $outerObject->direction . " OUTER JOIN ";
                $statement .= $outerObject->tableName . " ON ";
                $statement .= $outerObject->onCondition . " ";
            }
        }
        if (!empty($this->_filter)) {
            $statement .= "WHERE " . implode(' AND ', $this->_filter) . " ";
        }
        if (!empty($this->_groupBy)) {
            $statement .= "GROUP BY $this->_groupBy ";
        }
        if (!empty($this->_order)) {
            $statement .= "ORDER BY $this->_order ";
        }
        if (!empty($this->_limit)) {
            $statement .= "LIMIT $this->_limit ";
        }
        return $statement;
    }
}