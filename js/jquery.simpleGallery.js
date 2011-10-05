(function($){

	// The following method just figures out what you're doing from options
	// and creates a new instance of Gallery.  Everything should stick around
	// because it's scoped correctly.
	$.fn.simpleGallery = function(config)	{
		// Copy defaults to empty object literal and then overwrite changes from config
		var
			opts = $.extend(true,$.extend(true,{},$.fn.simpleGallery.defaults),config),
			thumbs = this.find('a'),
			container,
			img = $('<img class="simpleGalleryImg" />'),
			galleryObj
		;
		if(opts.container)	{
			container = $(opts.container);
		}
		else	{
			container = $('<div class="simpleGalleryImgWrapper"></div>');
		}
		this.wrap('<div class="simpleGallery"></div>');
		switch(opts.thumbDisplay)	{
		case 'before':
			this.after(container);
			break;
		case 'hide':
			this.before(container).hide();
			break;
		case 'after':
		default:
			this.before(container);
			break;
		}
		container.width(opts.width).height(opts.height);
		container.append(img);
		
		galleryObj = new Gallery(thumbs, container, img, opts.width, opts.height);
		
		//Last line of function; do not change:
		return this;
	};
	
	//Default settings:
	$.fn.simpleGallery.defaults = {
		width: 620, // gallery width in px (should be int)
		height: 465, // gallery height in px (should be int)
		thumbDisplay: 'after'	// before, after, or hide
	};
	
	// The actual class:
	function Gallery($thumbs,$container,$img,width,height){
		if(!$thumbs.length || !$container.length || !$img.length)	{
			return false;
		}
		var
			$nextButton = $('<span class="nextButton"></span>'),
			$prevButton = $('<span class="prevButton"></span>')
			imgRatio = width/height
		;

		function displayImage(href)	{
			$img.fadeOut('slow',function(){
				var tmpImg = new Image;
				tmpImg.onload = function(){
					var newRatio = this.width/this.height;
					if(newRatio < imgRatio)	{
						$img.css({
							width: (height * newRatio)+'px',
							height: height+'px',
							top: '0px'
						});
					}
					else	{
						$img.css({
							width: width+'px',
							height: (width/newRatio)+'px',
							top: ((height-(width/newRatio))/2)+'px'
						});
					}
					$img.attr('src',this.src);
					$img.fadeIn('slow');
				};
				tmpImg.src = href;
			});
		}
		
		function getCurrentIndex()	{
			for(var i = 0, max = $thumbs.length; i < max; i++)	{
				if($thumbs.eq(i).hasClass('current'))	{
					return i;
				}
			}
			return --i;
		}
		function nextImage()	{
			var current = getCurrentIndex();
			if(++current == $thumbs.length)	{
				displayImage($thumbs[0].href);
				$thumbs.removeClass('current');
				$thumbs.eq(0).addClass('current');
				return;
			}
			displayImage($thumbs[current].href);
			$thumbs.removeClass('current');
			$thumbs.eq(current).addClass('current');
		}
		
		function prevImage()	{
			var current = getCurrentIndex();
			if(--current < 0)	{
				displayImage($thumbs.last().attr('href'));
				$thumbs.removeClass('current');
				$thumbs.last().addClass('current');
				return;
			}
			displayImage($thumbs[current].href);
			$thumbs.removeClass('current');
			$thumbs.eq(current).addClass('current');
		}
		
		
		$thumbs.click(function(e){
			e.preventDefault();
			var newImgURL = this.href;
			$thumbs.removeClass('current');
			$(this).addClass('current');
			displayImage(newImgURL);
		});
		$prevButton.click(function(e){
			e.preventDefault();
			prevImage();
		});
		$nextButton.click(function(e){
			e.preventDefault();
			nextImage();
		});
		$container.append($prevButton).append($nextButton);
		
		if((navigator.userAgent.indexOf('iPhone') != -1) || (navigator.userAgent.indexOf('iPod') != -1) || (navigator.userAgent.indexOf('iPad') != -1))	{
			$nextButton.add($prevButton).addClass('mobile');
		}
		
		nextImage();

	}
})(jQuery);