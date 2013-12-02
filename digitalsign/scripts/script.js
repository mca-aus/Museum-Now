
$(document).ready(function() {

	positionElementsForWindowSize();
	$(window).resize(positionElementsForWindowSize);
	startAnimationSequence();
	loadCachedData();
	
});

var DEBUG_INFO = {};
var LANDSCAPE_ORIENTATION = "landscapeOrientation";
var PORTRAIT_ORIENTATION = "portraitOrientation";
var WIDE_SCREEN_ASPECT_RATIO_LANDSCAPE = 16 / 9.3;
var INSTAGRAM_AREA_VERTICAL_HEIGHT_RATIO_FOR_WIDE_SCREEN_ASPECT_RATIO_LANDSCAPE = 8 / 9.3;
var STANDARD_SCREEN_ASPECT_RATIO_LANDSCAPE = 4 / 3.15;
var INSTAGRAM_AREA_VERTICAL_HEIGHT_RATIO_FOR_STANDARD_SCREEN_ASPECT_RATIO_LANDSCAPE = 2.66 / 3.15;
var STANDARD_SCREEN_ASPECT_RATIO_PORTRAIT = 3 / 5;
var INSTAGRAM_AREA_VERTICAL_HEIGHT_RATIO_FOR_STANDARD_SCREEN_ASPECT_RATIO_PORTRAIT = 4.5 / 5;
var WIDE_SCREEN_ASPECT_RATIO_PORTRAIT = 9 / 20;
var INSTAGRAM_AREA_VERTICAL_HEIGHT_RATIO_FOR_WIDE_SCREEN_ASPECT_RATIO_PORTRAIT = 18 / 20;
var MARGIN_PERCENTAGE = 1;

// Set the min and max time delay between panel animations in seconds. The lower
// the maximum delay, the more frequent the panels animate
var SLIDING_PANEL_MIN_TIMEOUT = 5;
var SLIDING_PANEL_MAX_TIMEOUT = 30;

// Sets the amont of time that the digital sign reloads data from its cache.
// The default setting is 300 seconds (i.e., 5 minutes) - every 5 minutes
// the sign checks for new data from its cache
var CACHED_DATA_REFRESH_RATE = 300;

// Global variables
var currentOrientation;
var amountOfImagesToDisplay;

function loadCachedData()
{
	// Load Instagram photos
	var imageData = [];
	$.getJSON('../get/instagram-photos.json', {}, function(data) {
		
		console.log(data);
		
		var i;
		
		for (i in data)
		{
			imageData.push(data[i]);
		}
		shuffleArray(imageData);      

		$('.instagram-photo .front').each(function(i) {
			$(this).css({'background-image': 'url(' + imageData[i].images.locally_stored.url + ')'});
		});
		
		$('.instagram-photo .back').each(function(i) {
			$(this).css({'background-image': 'url(' + imageData[parseInt(i) + 7].images.locally_stored.url + ')'});
		});

	});
	
	// Load and populate Instagram user profile images
	$('.user-profile-image').remove();
	$.getJSON('../get/instagram-users.json', {}, function(data) {
		
		var profileImageURL,
			 profileUserID,
			 $userProfileImage;
		for (var i = 0; (i < data.length && i < 8); i++) 
		{
			profileUserID = data[i].user_id;
			profileImageURL = data[i].image.src;
			$userProfileImage = $('<img />').attr({
				'alt': profileUserID,
				'src': profileImageURL
			}).addClass('user-profile-image');
			$('.photos-provided-by').append($userProfileImage);
		}
	});

	// Reloads the cached data after a time-out, the app constantly checks
	// the data cache for new data
	setTimeout('loadCachedData()', CACHED_DATA_REFRESH_RATE * 1000);
}

function startAnimationSequence() 
{
	// Lift all .front panels to their opening positions
	$('.instagram-photo .front').css({'top': '-100%'});
	$('.instagram-photo .front').each(function() {
		setTimeout(closeFrontPanel, SLIDING_PANEL_MIN_TIMEOUT * 1000  + Math.random() * (SLIDING_PANEL_MAX_TIMEOUT - SLIDING_PANEL_MIN_TIMEOUT) * 1000, this);
	});
}

function shuffleArray(array)
{
	for (var i = array.length - 1; i > 0; i--) 
	{
		var j = Math.floor(Math.random() * (i + 1));
		var temp = array[i];
		array[i] = array[j];
		array[j] = temp;
	}
	return array;
}

function closeFrontPanel($instagramPhoto)
{
	$($instagramPhoto).animate({'top': '0%'}, 800, "easeOutCubic");
	setTimeout(openFrontPanel, SLIDING_PANEL_MIN_TIMEOUT * 1000  + Math.random() * (SLIDING_PANEL_MAX_TIMEOUT - SLIDING_PANEL_MIN_TIMEOUT) * 1000, $instagramPhoto);
}

function openFrontPanel($instagramPhoto)
{
	$($instagramPhoto).animate({'top': '-100%'}, 800, "easeInCubic");
	setTimeout(closeFrontPanel, SLIDING_PANEL_MIN_TIMEOUT * 1000  + Math.random() * (SLIDING_PANEL_MAX_TIMEOUT - SLIDING_PANEL_MIN_TIMEOUT) * 1000, $instagramPhoto);
}

/**
  * Positions the elements for a given window size
  * and orientation, allowing to adapt dynamically
  * if the viewport is presented in a landscape
  * or portrait orientation.
  */
function positionElementsForWindowSize() 
{
	// Calculate margin as a percentage of the width of the window
	margin = $(window).width() * (MARGIN_PERCENTAGE / 100);
	
	// Calculate the cutOffAspectRatios. cutOffAspectRations are the aspect
	// ratios that are the midpoints of the widescreen and non-widescreen ratios
	// for both portrait and landscape orientations
	var cutOffAspectRatioLandscape = (WIDE_SCREEN_ASPECT_RATIO_LANDSCAPE + STANDARD_SCREEN_ASPECT_RATIO_LANDSCAPE) / 2;
	var cutOffAspectRatioPortrait = (WIDE_SCREEN_ASPECT_RATIO_PORTRAIT + STANDARD_SCREEN_ASPECT_RATIO_PORTRAIT) / 1.5;
	
	// Determine if we're viewing in landscape or portrait
	// orientation

	var windowWidth = $(window).width() - (margin * 2);
	var windowHeight = $(window).height() - (margin * 2);
	var aspectRatioOfWindow = $(window).width() / $(window).height();

	if (aspectRatioOfWindow >= 1) 
	{
		currentOrientation = LANDSCAPE_ORIENTATION;
	}
	else if (aspectRatioOfWindow < 1)
	{
		currentOrientation = PORTRAIT_ORIENTATION;
	}

	// CSS Styles / Properties and calculations for the display's
	// container 
	var    containerWidth,
			 containerHeight,
			 containerVerticalPadding,
			 containerHorizontalPadding, 
			 containerCSSDimensions;

	// CSS Styles / Properties and calculations for the 
	// .instagram-photo-area
	var    instagramPhotoAreaWidth,
			 instagramPhotoAreaHeight

	// CSS Styles / Properties and calculations for the 
	// .banner and .footer
	var heightforBannerAndFooter,
		 bannerHeight,
		 footerHeight

	// Stores the grid configuration, whether we are
	// viewing 4 x 2 squares, 3 x 2 squares, etc.
	var gridConfiguration;

	if (currentOrientation == LANDSCAPE_ORIENTATION)
	{
		// Display as 16:9.5 if above cut-off aspect ratio
		if (aspectRatioOfWindow >= cutOffAspectRatioLandscape)
		{			
			if (aspectRatioOfWindow >= WIDE_SCREEN_ASPECT_RATIO_LANDSCAPE)
			{
				containerWidth = windowHeight * WIDE_SCREEN_ASPECT_RATIO_LANDSCAPE;
				containerHeight = windowHeight;
				containerHorizontalPadding = (windowWidth - containerWidth) / 2;
				containerVerticalPadding = 0;
			}
			// If window aspect ratio is less than 16:9
			else if (aspectRatioOfWindow < WIDE_SCREEN_ASPECT_RATIO_LANDSCAPE)
			{
				containerWidth = windowWidth;
				containerHeight = windowWidth * (1 / WIDE_SCREEN_ASPECT_RATIO_LANDSCAPE);
				containerHorizontalPadding = 0;
				containerVerticalPadding = (windowHeight - containerHeight) / 2;
			}

			instagramPhotoAreaHeight = INSTAGRAM_AREA_VERTICAL_HEIGHT_RATIO_FOR_WIDE_SCREEN_ASPECT_RATIO_LANDSCAPE * containerHeight;			
			gridConfiguration = {'cols': 4, 'rows': 2};

		}
		// Display as 8:7 if below cut-off aspect ratio
		else if (aspectRatioOfWindow < cutOffAspectRatioLandscape)
		{
			// If window aspect ratio is equal to or wider than 4:3
			if (aspectRatioOfWindow >= STANDARD_SCREEN_ASPECT_RATIO_LANDSCAPE)
			{
				containerWidth = windowHeight * STANDARD_SCREEN_ASPECT_RATIO_LANDSCAPE;
				containerHeight = windowHeight;
				containerHorizontalPadding = (windowWidth - containerWidth) / 2;
				containerVerticalPadding = 0;
			}
			// If window aspect ratio is less than 4:3
			else if (aspectRatioOfWindow < STANDARD_SCREEN_ASPECT_RATIO_LANDSCAPE)
			{
				containerWidth = windowWidth;
				containerHeight = windowWidth * (1 / STANDARD_SCREEN_ASPECT_RATIO_LANDSCAPE);
				containerHorizontalPadding = 0;
				containerVerticalPadding = (windowHeight - containerHeight) / 2;
			}

			instagramPhotoAreaHeight = INSTAGRAM_AREA_VERTICAL_HEIGHT_RATIO_FOR_STANDARD_SCREEN_ASPECT_RATIO_LANDSCAPE * containerHeight;
			gridConfiguration = {'cols': 3, 'rows': 2};

		}
		instagramPhotoAreaWidth = containerWidth;
	}
	else if (currentOrientation == PORTRAIT_ORIENTATION) 
	{
		if (aspectRatioOfWindow >= cutOffAspectRatioPortrait)
		{
			if (aspectRatioOfWindow >= STANDARD_SCREEN_ASPECT_RATIO_PORTRAIT)
			{
				containerWidth = windowHeight * STANDARD_SCREEN_ASPECT_RATIO_PORTRAIT;
				containerHeight = windowHeight;
				containerHorizontalPadding = (windowWidth - containerWidth) / 2;
				containerVerticalPadding = 0;
			}
			else if (aspectRatioOfWindow < STANDARD_SCREEN_ASPECT_RATIO_PORTRAIT)
			{
				containerWidth = windowWidth;
				containerHeight = windowWidth * (1 / STANDARD_SCREEN_ASPECT_RATIO_PORTRAIT);
				containerHorizontalPadding = 0;
				containerVerticalPadding = (windowHeight - containerHeight) / 2;
			}
			
			instagramPhotoAreaHeight = INSTAGRAM_AREA_VERTICAL_HEIGHT_RATIO_FOR_STANDARD_SCREEN_ASPECT_RATIO_PORTRAIT * containerHeight;
			gridConfiguration = {'cols': 2, 'rows': 3};
		}
		// Display as 9.5:16 if below the reciprocal of the cut-off ratio
		else if (aspectRatioOfWindow < cutOffAspectRatioPortrait)
		{
			if (aspectRatioOfWindow >= WIDE_SCREEN_ASPECT_RATIO_PORTRAIT)
			{
				containerWidth = windowHeight * WIDE_SCREEN_ASPECT_RATIO_PORTRAIT;
				containerHeight = windowHeight;
				containerHorizontalPadding = (windowWidth - containerWidth) / 2;
				containerVerticalPadding = 0;
			}
			else if (aspectRatioOfWindow < WIDE_SCREEN_ASPECT_RATIO_PORTRAIT)
			{
				containerWidth = windowWidth;
				containerHeight = windowWidth * (1 / WIDE_SCREEN_ASPECT_RATIO_PORTRAIT);
				containerHorizontalPadding = 0;
				containerVerticalPadding = (windowHeight - containerHeight) / 2;
			}
			
			instagramPhotoAreaHeight = INSTAGRAM_AREA_VERTICAL_HEIGHT_RATIO_FOR_WIDE_SCREEN_ASPECT_RATIO_PORTRAIT * containerHeight;
			gridConfiguration = {'cols': 2, 'rows': 4};
		}
		
		instagramPhotoAreaWidth = containerWidth;
	}
	
	// Calculate heights for .banner and .footer
	// .banner occupies two-thirds of the area
	// not occupied by the instagramPhotoArea.
	// .footer occupies the remaining one-third
	heightforBannerAndFooter = containerHeight - instagramPhotoAreaHeight;
	bannerHeight = Math.floor(heightforBannerAndFooter * (3 / 5));
	footerHeight = Math.floor(heightforBannerAndFooter * (2 / 5));
	
	// Determine the amount of images actually displayed on screen based on
	// the values of gridConfiguration
	amountOfImagesToDisplay = gridConfiguration.cols * gridConfiguration.rows;

	// Determine width and height of each .instagram-photo,
	// assume 10px padding between photos
	var instagramPhotoWidthAndHeight = (instagramPhotoAreaWidth - (10 * (gridConfiguration.cols - 1))) / gridConfiguration.cols;

	// Set layout CSS of .container
	$('.container').css({
		'margin': margin + 'px',
		'height': containerHeight + 'px',
		'top': containerVerticalPadding + 'px',
		'bottom': containerVerticalPadding + 'px',
		'left': containerHorizontalPadding + 'px',
		'right': containerHorizontalPadding  + 'px'
	});


	// Set layout CSS of .instagram-photo-area
	$('.instagram-photo-area').css({
		'height': instagramPhotoAreaHeight + 'px',
		'top': bannerHeight + 'px'
	});

	// Set layout CSS of .banner
	$('.banner').css({
		'top': (bannerHeight * 0.20) + 'px',
		'height': (bannerHeight * 0.60) + 'px'
	});
	
	// Set layout of CSS .footer, conditional on orientation configuration
	if (currentOrientation == LANDSCAPE_ORIENTATION && aspectRatioOfWindow < cutOffAspectRatioLandscape)
	{
		$('.footer').css({
			'height': (footerHeight * 0.50) + 'px',
			'bottom': (footerHeight * 0.30) + 'px'
		});
	}
	else if (currentOrientation == PORTRAIT_ORIENTATION && aspectRatioOfWindow < cutOffAspectRatioPortrait)
	{
		$('.footer').css({
			'height': (footerHeight * 0.50) + 'px',
			'bottom': (footerHeight * 0.20) + 'px'
		});
	}
	else
	{
		$('.footer').css({
			'height': (footerHeight * 0.50) + 'px',
			'bottom': (footerHeight * 0.30) + 'px'
		});
	}

	DEBUG_INFO['aspectRatioOfWindow'] = aspectRatioOfWindow;
	DEBUG_INFO['containerHeight'] = containerHeight;
	DEBUG_INFO['bannerHeight'] = bannerHeight;
	DEBUG_INFO['instagramPhotoAreaHeight'] = instagramPhotoAreaHeight;
	DEBUG_INFO['footerHeight'] = footerHeight;
	
	// Remove excessive instagram photos from layout if the screen is switching
	// to a smaller layout with less grid tiles
	var $instagramPhotos = $('.instagram-photo');
	$instagramPhotos.each(function(i) {
		if ((i + 1) > amountOfImagesToDisplay)
		{
			$(this).hide();
		}
	});
	
	// Set computed with and height, and populate .instagram-photo-area with grid
	// of .instagram-photos
	$('.instagram-photo').css({'width': instagramPhotoWidthAndHeight, 'height': instagramPhotoWidthAndHeight});
	var photoToSelect = 1;
	for (var rowCounter = 0; rowCounter < gridConfiguration.rows; rowCounter++)
	{
		for (var columnCounter = 0; columnCounter < gridConfiguration.cols; columnCounter++)
		{
			var xOffsetForInstagramPhoto = (columnCounter * instagramPhotoWidthAndHeight) + (columnCounter * 10);
			var yOffsetForInstagramPhoto = (rowCounter * instagramPhotoWidthAndHeight) + (rowCounter * 10);
			$('.instagram-photo:nth-child(' + photoToSelect + ')').css({'left': xOffsetForInstagramPhoto + 'px', 'top': yOffsetForInstagramPhoto + 'px'}).show();
			photoToSelect++;
		}
	}
	
	// Only display .photos-provided-by in portrait orientation
	var $photosProvidedBy = $('.photos-provided-by');
	if (currentOrientation == LANDSCAPE_ORIENTATION)
	{
		if (!$photosProvidedBy.is(':visible'))
			$photosProvidedBy.show();
	}
	if (currentOrientation == PORTRAIT_ORIENTATION)
	{
		if ($photosProvidedBy.is(':visible'))
			$photosProvidedBy.hide();
	}
	
	// Adjust font-size proportional to parent div for textual elements
	$('.banner .header, .photos-provided-by-caption, .see-more').each(function() {
		var fontSize = $(this).parent().height() * 0.95;
		$(this).css({'font-size': fontSize + 'px'});
	});

	// Reduce header text if not widescreen landscape aspect
	if (currentOrientation == LANDSCAPE_ORIENTATION && aspectRatioOfWindow > cutOffAspectRatioLandscape)
	{
		$(".banner .header span").css({'font-size': '100%'});
	}
	else
	{
		$(".banner .header span").css({'font-size': '60%'});
	}
	
//	// ** MCA Now Only **
//	// Set the background image of .banner .header depending on the width /
//	// wide-screen layout of the view.
//	// Display HeaderMCANowwide.jpg if in LANDSCAPE_ORIENTATION and above
//	// cutOffAspectRatioLandscape. Otherwise, display HeaderMCANow.jpg
//	if (currentOrientation == LANDSCAPE_ORIENTATION && aspectRatioOfWindow > cutOffAspectRatioLandscape)
//	{
//		$('.banner .header').css({'background-image': "url('img/banner-image-wide.jpg')"});
//	}
//	else
//	{
//		$('.banner .header').css({'background-image': "url('img/banner-image.jpg')"});
//	}
//	

// ** MCA Now Only **
// Manually adjust .photos-provided-by-caption to be equal to the height of
// .footer
$('.photos-provided-by-caption').height($('.footer').height());

}