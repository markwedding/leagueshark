$(document).ready(function(){
//Initialize tooltips
$('[data-toggle="tooltip"]').tooltip();

// Initialize datetimepickers
	// For leagues.php
	$(function() {
		$('#datetimepicker1').datetimepicker({
			format: 'YYYY-MM-DD'
		});
	});

	$(function() {
		$('#datetimepicker2').datetimepicker({
			format: 'YYYY-MM-DD'
		});
	});

	$(function() {
		$('#datetimepicker3').datetimepicker({
			format: 'YYYY-MM-DD LT'
		});
	});

//General style for elements that need a pointer
$(".pointer").hover(function(){
	$(this).css('cursor','pointer');
}, function(){
	$(this).css('cursor','auto');
});


// Navbar links
$("ul.navbar-nav li").hover(function(){
	$(this).addClass("active");
}, function(){
	$(this).removeClass("active");
});

// Form Alerts
setTimeout(function() {
	$('.form-alert').slideUp();
}, 4000);

// Validation
	// Ensure that passwords and confirm passwords match before submission
	$(document).on('submit','#signup-form',function(e){
		if($('#password').val()!=$('#cpassword').val()) {
			e.preventDefault();
			$('#password-error').text('*The passwords must match.');
		}
	});

	// Ensure that a team is selected if the player wants to join a team
	$('#chooseteam').click(function(e){
		if(!$('#team').val()) {
			e.preventDefault();
			$('#select-error').text('*Please select a team in order to join one.');
		}
	});

	// Ensure that a school is selected when a player signs up for a LeagueShark account
	$('#asubmit').click(function(e){
		if($('#playerschool').val()=='0') {
			e.preventDefault();
			$('#select-error').text('*Please select a school.');
		}
	});

// Validate login form
// $('#loginform').submit(function(e) {
// 	if($('#email').val().length == 0) {
// 		e.preventDefault();
// 		$('div.form-group:has(#email)').addClass('has-error');
// 		$('div.form-group:has(#email) small').text('*Required field');
// 	}
// });

// function checkLength(form,input) {
// 	$(form).submit(function(e) {
// 		if($(input).val().length == 0) {
// 			e.preventDefault();
// 			$('div.form-group:has('+ input + ')').addClass('has-error');
// 			$('div.form-group:has('+ input + ') small').text('*Required field');
// 		}
// 	});
// }	

//****signup.php****
	// Sign up choices
	$(".signup-choice").hover(function(){
		$(this).animate({backgroundColor: "#18BC9C", color: "#ffffff"}, 100);
	}, function(){
		$(this).animate({backgroundColor: "#ffffff", color: "#2C3E50"}, 100);
	});

	$(".signup-choice:contains('Administrator')").click(function(){
		$(location).attr("href","school-signup.php")
	})
	$(".signup-choice:contains('Player')").click(function(){
		$(location).attr("href","player-signup.php")
	});

//****teams.php****
	// Submit the league select form whenever the league select input is changed
	$('#teams-league').change(function(){
		$('#teams-select-league').submit();
	});

//****players.php & referees.php****
	// Passing list box values to the email form
	$("#emailbutton").click(function(){
		var league = $('#league option:selected').text();
		$('#hleague').attr('value',league);
	})
	$("#emailbutton").click(function(){
		var team = $('#team option:selected').text();
		$('#hteam').attr('value',team);
	});

	$("#emailbutton").click(function(){
		var league = $('#players-league option:selected').text();
		$('#hleague').attr('value',league);
	})
	$("#emailbutton").click(function(){
		var team = $('#players-team option:selected').text();
		$('#hteam').attr('value',team);
	});

	// Submit the form whenever the league or team select input is changed
	$('#players-league').change(function(){
		$('#players-team').val('all');
		$('#players-select').submit();
	});

	$('#players-team').change(function(){
		$('#players-select').submit();
	});

//****schedule.php****
	// Change Availability button to disabled when All teams are selected
	$('#schedule-team').on('change', function() {
		if($('#schedule-team option:selected').text()=='All') {
			$('#abutton').addClass('disabled');
		} else {
			$('#abutton').removeClass('disabled')
		}
	});

	// Submit the form whenever the league or team select input is changed
	$('#schedule-league').change(function(){
		$('#schedule-team').val('all');
		$('#schedule-select').submit();
	});

	$('#schedule-team').change(function(){
		$('#schedule-select').submit();
	});

	// Ensure that the a team is not scheduled to play itself
	$(document).on('submit','#addgame-form',function(e){
		if($('#hometeam').val()==$('#awayteam').val()) {
			e.preventDefault();
			$('#password-error').text('*A team cannot be scheduled to play itself.');
		}
	});

//****availability.php****
	// Check the checkbox when the table cell is clicked
	// $('#atable td').click(function() {
	// 	if(!$(this).find(':checkbox').is(':checked')) {
	// 		$(this).addClass('success');
	// 		$(this).find(':checkbox').prop('checked','true');
	// 	} else {
	// 		$(this).removeClass('success');
	// 		$(this).find(':checkbox').prop('checked','false');
	// 	}
	// });

	// Change the background color of a table cell when it is checked
	$('#atable :checkbox').change(function() {
		if(this.checked) {
			$('td').has(this).addClass('success');
		} else {
			$('td').has(this).removeClass('success');
		}
	});

//****myteams.php****
	// Scroll to the bottom of the message board on page load
	var d = $('#messageboard');
	d.scrollTop(d.prop("scrollHeight"));

	// Submit the team select form whenever the team select box is changed
	$('#myteams-team').change(function(){
		$('#myteams-select-team').submit();
	});

	// Make captain menu bar slide down on page load
	//$('#captain-menubar').slideDown('slow');

});
