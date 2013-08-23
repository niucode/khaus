<?php
class Layout extends Khaus_Controller_Layout
{
    public function boot()
    {
        $this->layout->title = Khaus_Config::application('title');
        $this->layout->base = str_replace('\\', '', DOCUMENT_URI);
        $this->layout->controller = $this->getControllerName();
        $this->layout->content = $this->getContent();
    }
}