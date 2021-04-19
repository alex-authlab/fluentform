<?php

namespace FluentForm\App\Services\FormBuilder\Components;

class Recaptcha extends BaseComponent
{
	/**
	 * Compile and echo the html element
	 * @param  array $data [element data]
	 * @param  stdClass $form [Form Object]
	 * @return viod
	 */
	public function compile($data, $form)
	{
        $elementName = $data['element'];
        $data = apply_filters('fluenform_rendering_field_data_'.$elementName, $data, $form);
        $key  = get_option('_fluentform_reCaptcha_details');
       
        if(\FluentForm\Framework\Helpers\ArrayHelper::get ($key,'api_version') == 'v3_invisible'){
            return $this->renderV3($data,$form,$key);
        }
        if($key && isset($key['siteKey'])) {
            $siteKey = $key['siteKey'];
        } else {
            $siteKey = '';
        }
        wp_enqueue_script(
			'google-recaptcha',
			'https://www.google.com/recaptcha/api.js',
			 array(),
			FLUENTFORM_VERSION,
			true
		);

		$key = get_option('_fluentform_reCaptcha_details');
		if($key && isset($key['siteKey'])) {
			$siteKey = $key['siteKey'];
		} else {
			$siteKey = '';
		}
  
		$recaptchaBlock = "<div
            data-sitekey='{$siteKey}'
            id='fluentform-recaptcha-{$form->id}'
            class='ff-el-recaptcha g-recaptcha'
            data-callback='fluentFormrecaptchaSuccessCallback'></div>";

		$label = '';
		if (!empty($data['settings']['label'])) {
			$label = "<div class='ff-el-input--label'><label>{$data['settings']['label']}</label></div>";
		}
		
		$el = "<div class='ff-el-input--content'><div data-fluent_id='".$form->id."' name='g-recaptcha-response'>{$recaptchaBlock}</div></div>";
		$atts = $this->buildAttributes(
			\FluentForm\Framework\Helpers\ArrayHelper::except($data['attributes'], 'name')
		);
		$html = "<div class='ff-el-group' {$atts}>{$label}{$el}</div>";
        echo apply_filters('fluenform_rendering_field_html_'.$elementName, $html, $data, $form);
    }
    
    private function renderV3($data, $form, $reCaptchaData)
    {
        $elementName = $data['element'];
        $key         = $reCaptchaData['siteKey'];
        $src         = "https://www.google.com/recaptcha/api.js?render={$key}&onload=ff_onload_recaptcha_v3_callback";
        add_action ( 'wp_footer', function () use ($src, $key, $form) {
            ?>
            <script src="<?php echo $src; ?>" async defer></script>
            <script type="text/javascript">
                function ff_onload_recaptcha_v3_callback() {
                    grecaptcha.ready(function () {
                        grecaptcha.execute('<?php echo $key; ?>', {
                            action : 'fluentform/_<?php echo $form->id; ?>'
                        }).then(function (token) {
                            var forms = jQuery('form.frm-fluent-form');
                            forms.find('#ff_recaptcha_v3_<?php echo $form->id; ?>').html('<input type="hidden" name="g-recaptcha-response" value="' + token + '" />');
                        });
                    });
                }
            
                jQuery(document).on('reInitExtras', '.<?php echo $form->instance_css_class; ?>', function () {
                    ff_onload_recaptcha_v3_callback();
                });
            </script>
    
        <?php }, 11 );
        
        $html        = "<div class='ff-el-input--content ff-el-is-error ff-el-recaptcha g-recaptcha ' id='ff_recaptcha_v3_{$form->id}'></div>";
        echo apply_filters ( 'fluenform_rendering_field_html_' . $elementName, $html, $data, $form );
        

    }
}
