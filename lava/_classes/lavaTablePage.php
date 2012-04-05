<?php
class lavaTablePage extends lavaPage
{
    public $dataSource;
    public $dataCache;
    public $displayOrder = array();


    function loadPage() {
        
        $this->addAction( "toolbarButtons" );
    }
    
    function setDataSource( $dataSource )
    {
        $this->dataSource = $dataSource;
        return $this->_pages( false );
    }

    function setDisplayOrder( $displayString )
    {
    	$this->displayOrder = explode( ";", $displayString );
        $dataSourceSlug = $this->dataSource;
        $hookTag = "_dataSourceAjax_row/dataSource:{$dataSourceSlug}";
        $this->addFilter( $hookTag, "doDisplayOrder" );
        return $this->_pages( false );
    }

    function doDisplayOrder( $row ) {
        $newRow = array();
        foreach( $this->displayOrder as $field ) {
            if( array_key_exists( $field, $row ) ) {
                $newRow[ $field ] = $row[ $field ];
            }
        }
        foreach( $row as $field => $value ) {
            if( !in_array( $field , $this->displayOrder) ) {
                $newRow[ $field ] = $value;
            }
        }

        return $newRow;
    }

    function displayPage() {
        if( is_null( $this->dataSource ) ) {
            $this->dieWith( "No data source specified for this page" );
        }
        $this->displayLoader();
    	$this->doTable();
    }

    function setOrderBy( $order ) {
        $this->_tables()->fetchTable( $this->dataSource )->setOrderBy( $order );
        return $this->_pages( false );
    }

    function displayLoader(){
        ?>
        <div class="lava-full-page-loader">
            <div class="lava-loader loading">
                <span class="child1"></span>
                <span class="child2"></span>
                <span class="child3"></span>
                <span class="child4"></span>
                <span class="child5"></span>
            </div>
        </div>
        <?php
    }

    function doTable() {
    	?>
    	<div class="lava-table-viewer" data-data-source="<?php echo $this->dataSource ?>" data-ajax-action="<?php echo $this->_slug( "data_source" ); ?>" data-ajax-nonce="<?php echo wp_create_nonce( $this->_slug( "data_source" ) ); ?>">
            <input class="lava-table-update-trigger" type="hidden" name="mrblobby" value="well anything could go here" />
			<table cellpadding="10" cellspacing="0">
			</table>
		</div>
    	<?php
    }

    function toolbarButtons()
    {
        ?>
        <div class="toolbar-block toolbar-overground">
            <button class="lava-btn lava-btn-action lava-btn-inline lava-btn-action-white lava-table-loader-refresh-button" data-clicked-text="<?php _e( "Refreshing", $this->_framework() ) ?>"><?php _e( "Refresh", $this->_framework() ) ?></button>
            <button class="lava-btn lava-btn-action lava-btn-inline lava-btn-action-white lava-btn-form-submit lava-btn-confirmation not-implemented" data-form="lava-table-reset" data-clicked-text="<?php _e( "Resetting", $this->_framework() ) ?>"><?php _e( "Reset Settings", $this->_framework() ) ?></button>
        </div>
        <?php
    }

}
?>