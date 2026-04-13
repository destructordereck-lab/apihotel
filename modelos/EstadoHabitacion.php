<?php
class EstadoHabitacion extends \DB\SQL\Mapper
{
    public function __construct(\DB\SQL $db)
    {
        parent::__construct($db, 'tb_estado_hab');
    }
}
