// JavaScript Document
$(function(){
	$("#datenaiss, #dateajout,").datepicker({
		changeMonth:true,
		defaultDate:"+1w",
		changeYear:true
	});
	$("#datedebut0, #datefin0, #datedebut1, #datefin1, #datedebut2, #datefin2, #datedebut3, #datefin3, #datedebut4, #datefin4, #datedebut5, #datefin5, #datedebut6, #datefin6, #datedebut7, #datefin7, #datedebut8, #datefin8, #datedebut9, #datefin9").datepicker({
		changeMonth:true,
		defaultDate:"+1w",
		changeYear:true
	});
	var dates = $( "#datedebut, #datefin" ).datepicker({
		defaultDate: "+1w",
		changeMonth: true,
		changeYear: true,
		onSelect: function( selectedDate ) {
			var option = this.id == "datedebut" ? "minDate" : "maxDate",
				instance = $( this ).data( "datepicker" ),
				date = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			dates.not( this ).datepicker( "option", option, date );
		}
	});
	$("#calendrierdujour, #chat, #timetable").datepicker();
});