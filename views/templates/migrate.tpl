<link rel="stylesheet" type="text/css" media="screen" href="<?=TAOBASE_WWW?>css/style.css"/>
<link rel="stylesheet" type="text/css" media="screen" href="<?=TAOBASE_WWW?>css/layout.css"/>
<link rel="stylesheet" href="<?= BASE_WWW ?>css/migration.css" />

<script type="text/javascript">
requirejs.config({
    config: {}
});
</script>

<div id="resultStorageMigration">
    
    <div class="nav">
           <h1><?=__('Results Data Migration Tool')?></h2>
    </div>

    <div id="sourceStorage">
        <h2><?=__('Source')?></h2>
            <?
                foreach (get_data('availableStorage') as $storage) {
            ?>
                <div>
                    <input type="checkbox" name="source" value="<?=$storage->getUri()?>"  />
                    <label for="source1"><?=$storage->getLabel()?></label>
                </div>
            <?
                }
            ?>
           
        
    </div>   
    <div id="operations">
        
                <div class="button" id="clone"><?=__('Clone Data')?></div>
                <div class="button" id="migrate"><?=__('Migrate Data')?></div>
        
    </div>
    
    <div id="targetStorage">
        <h2><?=__('Target')?></h2>

         <?
                foreach (get_data('availableStorage') as $storage) {
            ?>
                <div>
                    <input type="checkbox" name="source" value="<?=$storage->getUri()?>" />
                    <label for="source1"><?=$storage->getLabel()?></label>
                </div>
            <?
                }
            ?>
    <div id="migrationProgress">
        
    </div>
</div>
