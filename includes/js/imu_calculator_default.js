function imucSubmitForm(path) { 
    jQuery.ajax({
        type:"POST", 
        url: path + "/wp-admin/admin-ajax.php", 
        data:jQuery("#imuc-calcoloimu").serialize(),
        beforeSend:function(){
            jQuery("#spinner").show()
        },
        success:function(response){
            jQuery("#spinner").hide();
            jQuery("#imuc-calcoloimu").find(".result").html(response)
        }
    });
    return false
}

function sanitizeNumber(num){
    num = num.toString().replace(/\jQuery|\./g,'');
    num = num.toString().replace(/\,/g,'.');
    return num
}

function formatCurrency(val, applySanitize) {
    if( applySanitize == undefined){
        applySanitize = true;
    }else{
        applySanitize = false;
    }
    if(applySanitize) val = sanitizeNumber(val);
    if(isNaN(val)) val = "0";
    
    sign = (val == (val = Math.abs(val)));
    val = Math.floor(val*100+0.50000000001);
    cents = val%100;
    val = Math.floor(val/100).toString();
    if(cents<10)
        cents = "0" + cents;
    for (var i = 0; i < Math.floor((val.length-(1+i))/3); i++)
        val = val.substring(0,val.length-(4*i+3))+'.'+
        val.substring(val.length-(4*i+3));
    return (((sign)?'':'-') + val + ',' + cents);
}

function variazione_aliquota( new_val ){
    var max_val = 0.60;
    var min_val = 0.20;
    var option = jQuery("#form_coefficiente").val();
	
    var rel = jQuery("#form_aliquota").attr('rel')
    if(new_val == undefined && rel != undefined && rel != ''){
        new_val = rel;
    }

    if( option == "FAC267" || option == "FAD10" ){
        new_val = (new_val == undefined) ? 0.20 : new_val;
        max_val = 0.20;
        min_val = 0.10;
    }else{
        new_val = (new_val == undefined) ? 0.76 : new_val;
        max_val = 1.06;
        min_val = 0.46;
		
        if( jQuery(".prima_casa").is(':visible') ){
            if( jQuery('#form_prima_casa_si').is(":checked") ){
                new_val = (new_val == undefined || new_val == 0.76) ? 0.40 : new_val;
                max_val = 0.60;
                min_val = 0.20;
            }
        }
    }


    if( new_val > max_val ){
        new_val = max_val;
    }

    if( new_val < min_val ){
        new_val = min_val;
    }
	
    jQuery("#form_aliquota").val( new_val );
    jQuery("#form_aliquota").change();
}

function controlla_contitolari(){
    var valore = jQuery("#form_quota_possesso").val();
	
    if( jQuery('#form_prima_casa_si').is(":checked") && jQuery("#form_coefficiente").val() == 'A' ){
        if( valore < 100){
            jQuery(".contitolari_row").show();
        }else{
            jQuery(".contitolari_row").hide();
            jQuery("#form_contitolari").val('2');
        }
    }else{
        jQuery(".contitolari_row").hide();
        jQuery("#form_contitolari").val('2');
    }
}

function transformTypedChar(charStr,fromChar,toChar) {
    if(fromChar == undefined || fromChar == null) fromChar = ",";
    if(toChar == undefined || toChar == null) toChar = ".";
    return charStr == fromChar ? toChar : charStr;
}

function getInputSelection(el) {
    var start = 0, end = 0, normalizedValue, range,
    textInputRange, len, endRange;

    if (typeof el.selectionStart == "number" && typeof el.selectionEnd == "number") {
        start = el.selectionStart;
        end = el.selectionEnd;
    } else {
        range = document.selection.createRange();

        if (range && range.parentElement() == el) {
            len = el.value.length;
            normalizedValue = el.value.replace(/\r\n/g, "\n");

            
            textInputRange = el.createTextRange();
            textInputRange.moveToBookmark(range.getBookmark());

            endRange = el.createTextRange();
            endRange.collapse(false);

            if (textInputRange.compareEndPoints("StartToEnd", endRange) > -1) {
                start = end = len;
            } else {
                start = -textInputRange.moveStart("character", -len);
                start += normalizedValue.slice(0, start).split("\n").length - 1;

                if (textInputRange.compareEndPoints("EndToEnd", endRange) > -1) {
                    end = len;
                } else {
                    end = -textInputRange.moveEnd("character", -len);
                    end += normalizedValue.slice(0, end).split("\n").length - 1;
                }
            }
        }
    }

    return {
        start: start,
        end: end
    };
}
function controllo_eta_figli(){
    var max_val = jQuery("#form_mesi_possesso").val();
    
    var n_figli = jQuery("#form_n_figli").val();

        for(i=1; i <= n_figli; i++){
            if(jQuery("#imuc_eta_figlio_" + i + " input").is(":visible")) {
                
                if(jQuery("#imuc_eta_figlio_" + i + " input").val() > max_val) {
                    jQuery("#imuc_eta_figlio_" + i + " input").val(max_val);
                }
            }
            
        }
    
} 

function offsetToRangeCharacterMove(el, offset) {
    return offset - (el.value.slice(0, offset).split("\r\n").length - 1);
}

function setInputSelection(el, startOffset, endOffset) {
    el.focus();
    if (typeof el.selectionStart == "number" && typeof el.selectionEnd == "number") {
        el.selectionStart = startOffset;
        el.selectionEnd = endOffset;
    } else {
        var range = el.createTextRange();
        var startCharMove = offsetToRangeCharacterMove(el, startOffset);
        range.collapse(true);
        if (startOffset == endOffset) {
            range.move("character", startCharMove);
        } else {
            range.moveEnd("character", offsetToRangeCharacterMove(el, endOffset));
            range.moveStart("character", startCharMove);
        }
        range.select();
    }
}

jQuery('document').ready(function(){	
    /*
	if(jQuery.browser.msie) {
		jQuery("#form_coefficiente")
		.mousedown(function(){
			jQuery("#label_form_coefficiente").hide();
		    if(jQuery(this).css("width") != "auto") {
		        var width = jQuery(this).width();
		        jQuery(this).data("origWidth", jQuery(this).css("width"))
		               .css("width", "auto");

		        if(jQuery(this).width() < width) {
		            jQuery(this).css("width", jQuery(this).data("origWidth"));
					jQuery("#label_form_coefficiente").show();
		        }
		    }
		})

		.blur(function(){
		    jQuery(this).css("width", jQuery(this).data("origWidth"));
			jQuery("#label_form_coefficiente").show();
		})

		.change(function(){
		    jQuery(this).css("width", jQuery(this).data("origWidth"));
			jQuery("#label_form_coefficiente").show();
		});
	}
	*/
       
    jQuery("#spinner").hide();
       
    jQuery("#form_aliquota").keyup(function(){
        if( jQuery(this).val() != '0.' && jQuery(this).val() != '0' && jQuery(this).val() != '.' && jQuery(this).val() != '' && jQuery(this).val() != undefined ){
            variazione_aliquota(jQuery(this).val());
        }
    });
	
    jQuery("#form_rendita").keypress(function(evt) {
        if (evt.which) {
            var charStr = String.fromCharCode(evt.which);
            var transformedChar = transformTypedChar(charStr,".",",");
            if (transformedChar != charStr) {
                var sel = getInputSelection(this), val = this.value;
                this.value = val.slice(0, sel.start) + transformedChar + val.slice(sel.end);
                setInputSelection(this, sel.start + 1, sel.start + 1);
                return false;
            }
			
            var charCode = (evt.which) ? evt.which : event.keyCode
            if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 44)
                return false;
        }
    });
	
    jQuery("#form_aliquota,#form_quota_possesso").keypress(function(evt) {
        if (evt.which) {
            var charStr = String.fromCharCode(evt.which);
            var transformedChar = transformTypedChar(charStr);
            if (transformedChar != charStr) {
                var sel = getInputSelection(this), val = this.value;
                this.value = val.slice(0, sel.start) + transformedChar + val.slice(sel.end);
                setInputSelection(this, sel.start + 1, sel.start + 1);
                return false;
            }
			
            var charCode = (evt.which) ? evt.which : event.keyCode
            //accetto solo i numeri e il punto
            if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46)
                return false;
        }
    });
  
    jQuery("#form_n_figli").keyup(function(){
        var n_figli = jQuery(this).val();
        var default_value = jQuery("#form_mesi_possesso").val();
        if( n_figli > 8){
            jQuery(this).val(8);
            n_figli = 8;
        }
        if( n_figli > 0){
            jQuery("#imuc_eta_figli").show();
        }else{
            jQuery("#imuc_eta_figli").hide();
        }
        jQuery(".imuc_eta_figli").hide();
        jQuery(".imuc_eta_figli input").val(0);
        for(i=1; i <= n_figli; i++){
            jQuery("#imuc_eta_figlio_" + i + " input").val(default_value);
            jQuery("#imuc_eta_figlio_" + i).show();
        }
    }); 
        
    jQuery("#imuc-calcoloimu input").keypress(function(evt){
        var charCode =  evt.keyCode ? evt.keyCode : evt.which ? evt.which : evt.charCode;
        if(charCode == 13)
            return false;
    });
	
    jQuery("#form_n_figli,#form_contitolari,#form_mesi_possesso").keypress(function(evt){
        var charCode =  evt.keyCode ? evt.keyCode : evt.which ? evt.which : evt.charCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57))
            return false;
			
        return true;
    });
	
    jQuery("#imuc-calcoloimu input").keyup(function(){
        //        imucSubmitForm();
        });

    jQuery("#imuc-calcoloimu input").change(function(){
        //        imucSubmitForm();
        });
	
    jQuery("#form_quota_possesso").keyup(function(){
        var valore = parseInt(jQuery(this).val());
        if( valore > 100){
            jQuery(this).val(100);
            valore = 100;
        }
		
        if( valore < 0){
            jQuery(this).val(0);
            valore = 0;
        }
        controlla_contitolari();
    //        calcolo_imu();
    });
	
    jQuery("#form_mesi_possesso").keyup(function(){
        var valore = parseInt(jQuery(this).val());
        if( valore > 12){
            jQuery(this).val(12);
        //            imucSubmitForm() ;
        }
		
        if( valore < 0){
            jQuery(this).val(1);
        //            imucSubmitForm() ;
        }
        controllo_eta_figli();
    });

	
    jQuery("#form_coefficiente").change(function(){
        var option = jQuery(this).val();
		
        if( option == "C2" || option == "C6" || option == "C7"){
            jQuery("#label_prima_casa").html("Pertinenza di abitazione principale");
            jQuery("#label_prima_casa").css('line-height','normal');
        }else{
            jQuery("#label_prima_casa").html("Abitazione principale?");
            jQuery("#label_prima_casa").css('line-height','normal');
        }
		
        if( option != "A"){
            jQuery("#form_prima_casa_si").attr('checked',false);
            jQuery("#form_prima_casa_no").click();
            
            if( option == "C2" || option == "C6" || option == "C7"){
                jQuery("input[name=prima_casa]").change();
                if( ! jQuery(".prima_casa").is(':visible')){
                    jQuery(".prima_casa").show();
                    jQuery("input[name=prima_casa]").change();
                }
            }else{
                if(jQuery(".prima_casa").is(':visible')){
                    jQuery(".prima_casa,.figli").hide();
                    jQuery("input[name=prima_casa]").change();
                }
            }
        }else{
            jQuery(".prima_casa,.figli").show();
            jQuery("#form_prima_casa_no").attr('checked',false);
            jQuery("#form_prima_casa_si").click();
            jQuery("input[name=prima_casa]").change();
        }

        if( option == "TA" || option == "AE"){
            jQuery(".detrazione_base_imponibile").hide();
		
            if( option == "TA"){
                jQuery(".coltivatore_diretto").show();
                jQuery("#label_rendita").html("Reddito dominicale");
            }else if( option == "AE"){
                jQuery("#label_rendita").html("Valore dell'area");
            }else{
                jQuery(".coltivatore_diretto").hide();
                jQuery("#label_rendita").html("Rendita catastale");
            }
			
        }else{
            jQuery(".detrazione_base_imponibile").show();
            jQuery(".coltivatore_diretto").hide();
            jQuery("#label_rendita").html("Rendita catastale");
        }
		
        jQuery("input[name=quota_possesso]").keyup();
        jQuery(".help-box").hide();
        jQuery(".help-box.default").show();
		
        variazione_aliquota();
		
    });
	
    jQuery("input[name=prima_casa]").change(function(){
        variazione_aliquota();
		
        if( jQuery('#form_prima_casa_si').is(":checked") && jQuery("#form_coefficiente").val() == 'A' ){
            jQuery(".figli").show();
        }else{
            jQuery(".figli").hide();
        }
		
        controlla_contitolari();
    
    });
	
    jQuery(".aliquota_link").click(function(){
        var rel = jQuery(this).attr('rel');
        var new_val = jQuery("#form_aliquota").val();
        if( rel == 'su'){
            new_val = ( parseFloat( new_val ) + 0.01 ).toFixed(2);
        }else{
            new_val = ( parseFloat( new_val ) - 0.01 ).toFixed(2);
        }
		
        variazione_aliquota(new_val);
		
        return false;
		
    
    });
	
    jQuery("#form_coefficiente").change();
    	
    jQuery("#imuc-calcoloimu").submit(function(){
        var rendita = jQuery("#form_rendita").val();
        if(rendita == undefined || rendita == '0,00' || rendita == 0) return false;
    });
});

jQuery.fn.tipbox = function(content, allowHtml, className){
    jQuery.fn.tipbox.created.id = "imc_calculator_tipBox";
    jQuery("body").append(jQuery.fn.tipbox.created);
	
    var tipBox = jQuery(jQuery.fn.tipbox.created);
    tipBox.css({
        "position":"absolute",
        "display":"none"
    });

    function tipBoxShow(e){
        tipBox.css({
            "display":"block", 
            "top":e.pageY+16, 
            "left":e.pageX
        });
    }
    function tipBoxHide(){
        tipBox.css({
            "display":"none"
        });
    }

    this.each(function(){
        jQuery(this).mousemove(function(e){
            tipBoxShow(e);
			
            if(allowHtml)
                tipBox.html(content);
            else
                tipBox.text(content);
			
            tipBox.removeClass();
			
            if(className) tipBox.addClass(className);
        });
        jQuery(this).mouseout(function(){
            tipBoxHide();
        });
    });	
};

jQuery.fn.tipbox.created = document.createElement("img");
