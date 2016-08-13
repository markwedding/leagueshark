$(document).ready(function(){
//Initialize tooltips
$('[data-toggle="tooltip"]').tooltip()


// Navbar links
$("ul.nav li").hover(function(){
	$(this).addClass("active");
}, function(){
	$(this).removeClass("active");
});

//****signup.php****
	// Sign up choices
	$(".signup-choice").hover(function(){
		$(this).animate({backgroundColor: "#18BC9C", color: "#ffffff"}, 100);
		$(this).css('cursor','pointer');
	}, function(){
		$(this).animate({backgroundColor: "#ffffff", color: "#2C3E50"}, 100);
		$(this).css('cursor','auto');
	})

	$(".signup-choice:contains('Administrator')").click(function(){
		$(location).attr("href","school-signup.php")
	})
	$(".signup-choice:contains('Player')").click(function(){
		$(location).attr("href","player-signup.php")
	})
});
