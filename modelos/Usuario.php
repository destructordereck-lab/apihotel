<?php
class Usuario extends \DB\SQL\Mapper
{
    public function __construct(\DB\SQL $db)
    {
        parent::__construct($db, 'tb_usuarios');
    }
}
