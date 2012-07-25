<?php if(!defined('IN_MAUOS')) exit('Access Denied'); hookscriptoutput('test');?>
<?php $_G['home_tpl_titles'] = array($app['appname']);?><?php include template('common/header'); ?><div id="pt" class="bm cl">
<div class="z">
<a href="./" class="nvhm" title=""><?php echo $_G['setting']['bbname'];?></a> <em>&rsaquo;</em><?php echo $_G['setting']['navs']['5']['navname'];?>
</div>
</div>
<script src="http://static.manyou.com/scripts/my_iframe.js" type="text/javascript"></script>
<script language="javascript">
var prefixURL = "<?php echo $my_prefix;?>";
var suffixURL = "<?php echo $my_suffix;?>";
var queryString = '';
var url = "http://apps.manyou.com/<?php echo $my_appId;?>";
var oldHash = null;
var timer = null;
var appId = '<?php echo $my_appId;?>';

var server = new MyXD.Server("ifm0");
server.registHandler('iframeHasLoaded');
server.registHandler('setTitle');
server.start();


function iframeHasLoaded(ifm_id) {
MyXD.Util.showIframe(ifm_id);
document.getElementById('loading').style.display = 'none';
}

function  htmlspecialchars_decode(string) {
string = string.toString();
string = string.replace(/&amp;/g, '&');
string = string.replace(/&lt;/g, '<');
string = string.replace(/&gt;/g, '>');
string = string.replace(/&quot;/g, '"');
string = string.replace(/&#039;/g, "'");
return string;

}
function setTitle(x) {<?php $my_site_name=dhtmlspecialchars($_G['setting']['sitename'], ENT_QUOTES);?>var my_site_name = htmlspecialchars_decode('<?php echo $my_site_name;?>');

x = htmlspecialchars_decode(x);
document.title = x + my_site_name;
}

</script>
<?php if($isFullscreen==1) { ?>
<div id="ct" class="wp cl">
<div class="mn">
<?php if(!empty($_G['setting']['pluginhooks']['userapp_app_top'])) echo $_G['setting']['pluginhooks']['userapp_app_top'];?>
<div id="mx2note" style="display: none; padding: 150px 0; text-align: center; background-color: #FFFFBF; color: #DB0000; letter-spacing: 1px;">

</div>
<div id="loading" style="display: block; padding: 100px 0; text-align: center; color: #999;">
<img src="<?php echo IMGDIR;?>/loading.gif" alt="loading..." class="vm" /> 
</div>
<iframe id="ifm0" frameborder="0" width="970" scrolling="no" height="810" style="position: absolute; top: -5000px; left: -5000px;" src="<?php echo $url;?>"></iframe>
<?php if(!empty($_G['setting']['pluginhooks']['userapp_app_bottom'])) echo $_G['setting']['pluginhooks']['userapp_app_bottom'];?>
</div>
<?php } else { ?>
<div id="ct" class="wp ct2_a cl">
<div class="mn">
<div id="mx2note" style="display: none; padding: 150px 0; text-align: center; background-color: #FFFFBF; color: #DB0000; letter-spacing: 1px;">

</div>
<div id="loading" style="display: block; padding: 100px 0; text-align: center; color: #999;">
<img src="<?php echo IMGDIR;?>/loading.gif" alt="loading..." class="vm" /> 
</div>
<iframe id="ifm0" frameborder="0" width="810" scrolling="no" height="810" style="position: absolute; top: -5000px; left: -5000px;" src="<?php echo $url;?>"></iframe>
</div>
<div class="appl"></div>
<?php } ?>
</div>
<script type="text/javascript">
if(top.location != location) {
top.location.href = location.href;
}
</script><?php include template('common/footer'); ?>