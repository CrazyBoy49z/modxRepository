<?php
/*
 * Выводим данные по всем репозиториям для дерева разделов
 */
require_once  dirname(__FILE__).'/response.class.php';
 
class modxRepositoryRepository extends modxRepositoryResponse{
    var $params = array();
    var $root = '<repositories/>';
    var $parent = null;

    public function process(){
        
        if(!$this->parent = $this->modx->getOption('modxRepository.handler_doc_id', null, false)){
            return $this->failure('Не был получен ID раздела');
        }
        
        $data = $this->getData();
        
        return $this->toXML($data, $this->params);
    }
    
    function getData(){
        $result = array(); 
        $params = array_merge($this->getProperties(), array(
            'where' => array(
                'parent'    => $this->parent,
            )
        ));
        
        $response = $this->runProcessor('repository/getrepositories',$params );
        if(!$repositories = $response->getResponse()){
            $this->failure('Failure get repositories');
            return false;
        }
        foreach($repositories as $repository){
            $result[] = array(
                'repository' => $this->prepareRow($repository->toArray()),
            );
        } 
        $this->params = array(
            'type'  => 'array',
            'of'    => '1',
            'page'  => '1',
            'total' =>  count($repositories),
        );
	    
        return $result;
    }
    
    function prepareRow($data){ 
        
        return array(
            'description'   => $data['description'],
            'templated'     => $data['templated'],
            'rank'          => $data['menuindex'],
            'packages'      => $data['packages'],
            'createdon'     => $data['createdon'],
            'name' =>   $data['pagetitle'],
            'id'    => $data['object_id'],
        );
    } 
}
return 'modxRepositoryRepository';
?>
