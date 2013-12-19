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
           <div>
               <input type="checkbox" id="source1" name="source" value="0" disabled="disabled" />
               <label for="source1"><?=__('No Storage')?></label>
            </div>
            <div>
               <input type="checkbox" id="source2" name="source" value="1" checked="checked" />
               <label for="source2"><?=__('Ontology Storage')?></label>
            </div>
            <div>
               <input type="checkbox" id="source3" name="source" value="" checked="checked" />
               <label for="source3"><?=__('Key Value Storage')?></label>
            </div>
        
    </div>   
    <div id="operations">
        
                <div class="button" id="clone"><?=__('Clone Data')?></div>
                <div class="button" id="migrate"><?=__('Migrate Data')?></div>
        
    </div>
    
    <div id="targetStorage">
        <h2><?=__('Target')?></h2>

        <div>
               <input type="checkbox" id="target1" name="source" value="0" disabled="disabled" />
               <label for="target1"><?=__('No Storage')?></label>
            </div>
             <div>
               <input type="checkbox" id="target2" name="source" value="1" checked="checked" />
               <label for="target2"><?=__('Ontology Storage')?></label>
            </div>
            <div>
               <input type="checkbox" id="target3" name="source" value="" checked="checked" />
               <label for="target3"><?=__('Key Value Storage')?></label>
            </div>

    </div>   
    <div id="migrationProgress">
        
    </div>
</div>
