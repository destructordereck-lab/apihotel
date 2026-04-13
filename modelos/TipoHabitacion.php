<?php
class TipoHabitacion extends \DB\SQL\Mapper
{
    public function __construct(\DB\SQL $db)
    {
        parent::__construct($db, 'tb_ubicacion_hab');
    }
}
