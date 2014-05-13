var step = 1;
$(document).ready(function(){
    $('a[href=#step1]').on('click', function(){
        $('#begin_install').hide();
        $('#step1').show();
        $('.step').html('1/3');
    });
    $('a[href=#connect_db]').on('click', function(){
        $('.load_connect').show();
        $('#form_connect').submit();
	});
    $('a[href=#datatables]').on('click', function(){
        $('.load').show();
        $('#InstallTables').submit();
    });
    $('a[href=#next]').on('click', function(){
        if(step < 3)step++;
        if(step === 2){
            $('#step1').hide();
            $('#step2').show();
            $('.step').html('2/3');
        }else if(step === 3){
            $('#step2').hide();
            $('#step3').show();
            $('.step').html('3/3');
        }
    });
    $('a[href=#back]').on('click', function(){
        if(step > 1)step--;
        if(step === 1){
            $('#step2').hide();
            $('#step1').show();
            $('.step').html('1/3');
        }else if(step === 2){
            $('#step3').hide();
            $('#step2').show();
            $('.step').html('2/3');
        }
    });
    $('a[href=#language]').on('click', function(){
        $('#input_language').val($(this).attr('id'));
        $('#ch_language').submit();
    });
});