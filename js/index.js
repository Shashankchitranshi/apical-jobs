$('.owl-carousel').owlCarousel({
    loop: true,
    margin: 10,
    nav: true,
    autoplay: true,
    items: 1,
    responsive: {
        0: {
            items: 1
        },
        600: {
            items: 1
        },
        1000: {
            items: 1
        }
    },
    navText: ["<img src='images/left-arrow.png'>", "<img src='images/right-arrow.png'>"]
});

//Carousel****end*****

$('#myTabs a').click(function(e) {
    e.preventDefault()
    $(this).tab('show')
});

//Tab****end*****
if (screen.width <= 767) {
    $(".navbar-default").addClass("blueHeader");
}
$(window).scroll(function() {
    var scroll = $(window).scrollTop();
    var screenWidth = screen.width;
    if (screen.width <= 767) {
        if (scroll >= 0) {
            $(".headercolor").addClass("blueHeader");
        } else {
            $(".headercolor").removeClass("blueHeader");
        }
    } else if (screen.width > 767 || screen.width <= 991) {
        $(".blue-header").addClass("blueHeader")
        if (scroll >= 150) {
            $(".headercolor").addClass("blueHeader");
        } else {
            $(".headercolor").removeClass("blueHeader");
        }
    } else {
        $(".blue-header").addClass("blueHeader")
        if (scroll >= 400) {
            $(".headercolor").addClass("blueHeader");
        } else {
            $(".headercolor").removeClass("blueHeader");
        }
    }
});
//Header Scroll Functionality****end*****

$(".terms-agreement input").click(function() {
        $(this).toggleClass("checked")
    })
    // Choose file

$('#chooseFile').bind('change', function() {
    var filename = $("#chooseFile").val();
    if (/^\s*$/.test(filename)) {
        $(".file-upload").removeClass('active');
        $("#noFile").text("No file chosen...");
    } else {
        $(".file-upload").addClass('active');
        $("#noFile").text(filename.replace("C:\\fakepath\\", ""));
    }
});