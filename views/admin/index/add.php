<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */

#queue_js_file('contribution-public-form');
#queue_js_string('enableContributionAjaxForm("contribution/type-form");');

$head = array('title' => 'Build folktale',
              'bodyclass' => 'folktale-builder');
echo head($head);
#queue_js_file('contribution-public-form'); ?>


<div id="primary">
<?php echo flash(); ?>
    
    <h1><?php echo $head['title']; ?></h1>

        <form method="post" action="" enctype="multipart/form-data">
            <fieldset id="contribution-item-metadata">
                <div class="inputs">
                    <label for="contribution-type">What type of item do you want to contribute?</label>
                    <?php #$options = get_table_options('ContributionType' ); ?>
                    <?php #$typeId = $type ? $type->id : '' ; ?>
                    <?php echo $this->formSelect( 'contribution_type', 1, array('multiple' => false, 'id' => 'contribution-type') , $options); ?>
                    <input type="submit" name="submit-type" id="submit-type" value="Select" />
                </div>
                <div id="contribution-type-form">
                <?php if (isset($typeForm)): echo $typeForm; endif; ?>
                </div>
            </fieldset>
            <fieldset id="contribution-confirm-submit" <?php if (!isset($typeForm)) { echo 'style="display: none;"'; }?>>
                <?php if(isset($captchaScript)): ?><div id="captcha" class="inputs"><?php echo $captchaScript; ?></div><?php endif; ?>
                <div class="inputs">
                    <?php $public = isset($_POST['contribution-public']) ? $_POST['contribution-public'] : 0; ?>
                    <?php echo $this->formCheckbox('contribution-public', $public, null, array('1', '0')); ?>
                    <?php echo $this->formLabel('contribution-public', 'Publish my contribution on the web.'); ?>
                </div>
                <?php echo $this->formSubmit('form-submit', 'Contribute', array('class' => 'submitinput')); ?>
            </fieldset>
        </form>
</div>
<?php echo foot();
