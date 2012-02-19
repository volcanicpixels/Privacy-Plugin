<?php
class lavaTablePage extends lavaPage
{
    function setDataSource( $dataSource )
    {
        //$this->dataSource = $this->_tables()->fetchTable( $dataSource );
        return $this->_pages( false );
    }

    function setDisplayOrder( $displayString )
    {
        return $this->_pages( false );
    }
}
?>