<?php
class ErrorController extends Khaus_Controller_Action
{
    public function init()
    {
        
    }

    public function errorAction()
    {
        $exceptionTrace = $this->exception->getTrace();
        $this->view->code = $this->exception->getCode();
        $this->view->message = $this->exception->getMessage();
        $this->view->line = $this->exception->getLine();
        $this->view->file = $this->exception->getFile();
        $this->view->trace = $exceptionTrace;
        $this->view->subWarning = '';
        // si es un error de PDO, muestra la consulta con error
        if ($this->exception instanceof PDOException) {
            $query = $exceptionTrace[0]['args'][0];
            $this->view->subWarning = $query;
        }
    }
}