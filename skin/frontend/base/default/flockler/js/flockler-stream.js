/* $jsonp based on jsonp.js by Przemek Sobstel, licensed under the MIT license
 * https://github.com/sobstel/jsonp.js
 * Modified to automatically adapt to using the same callback name multiple times for different objects
 */
var $jsonp = (function(){
  var that = {};

  that.index = 0;

  that.send = function(src, options) {
    var callback_name = '__jsonp_callback_' + this.index,
        on_success = options.onSuccess || function(){},
        on_timeout = options.onTimeout || function(){},
        timeout = options.timeout || 10;

    var timeout_trigger = window.setTimeout(function(){
      window[callback_name] = function(){};
      on_timeout();
    }, timeout * 1000);

    window[callback_name] = function(data){
      window.clearTimeout(timeout_trigger);
      on_success(data);
    };

    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.async = true;
    script.src = src + (src.indexOf('?') === -1 ? '?' : '&') + 'callback=' + callback_name;

    document.getElementsByTagName('head')[0].appendChild(script);
    this.index++;
  };

  return that;
})();

;(function() {
  // Make sure we're using jQuery (not e.g. Prototype)
  var $ = jQuery;

  if (!$) {
    console.log('jQuery not found! Aborting.');
    return;
  }

  // Patch in a stub for jQuery.Deferred. imagesLoaded assumes it exists for jQuery, but it doesn't for jQuery 1.4, which is bundled with Drupal 7.
  // We are only using imagesLoaded the vanilla JS way, so the jQuery way not working is not a concern at the moment.
  if (!($.Deferred)) {
    $.extend({
      Deferred: function() {
        this.reject = this.promise = this.resolve = function() {};
      }
    });
  }

  $(document).ready(function () {
    var $walls = $('.flockler-wall-widget-container .flockler-wall');
    var $singleItem = $('.flockler-post-single-container');

    if (!$walls.length && !$singleItem.length) {
      // No widgets found, bail
      return;
    }


    var pureIntegerString = new RegExp("^\\d+$");
    var percentageString = new RegExp("^\\d+(\.\\d+)?%$");
    var updateItemHeightsOnImageLoad = function (el) {
      el.find('.flockler-wall-item__media img, .flockler-wall-item__video img, .flockler-wall-item__body img, .flockler-wall-item__link-preview img')
          .not('.image-loaded').each(function(i, image) {
            imagesLoaded(image, function() {
              el.msnry.layout();
              $(image).addClass('image-loaded');
            });
          });
    };

    var on = function (event, selector, fn) {
      if (!!$.fn.on) {
        // jQuery 1.7+
        $(document).on(event, selector, fn);
      } else if (!!$.fn.delegate) {
        // jQuery 1.4.3+
        $(document).delegate(selector, event, fn);
      } else if (!!$.fn.live) {
        // jQuery 1.3+
        $(selector).live(event, fn);
      }
    };

    function findHHandWW() {
      imgHeight = this.height;imgWidth = this.width;return true;
    }

    $walls.each(function(i, container) {
      var el = $(container);

      var itemCount = el.data('flockler-item-count');
      if (!pureIntegerString.test(itemCount)) {
        itemCount = 10;
      }

      var ajaxData = new Object();
      ajaxData.ajax_url = el.data('flockler-url');

      var webhookID = el.data('flockler-webhook-id');
      var siteID = el.data('flockler-site-id');
      var sectionID = el.data('flockler-section-id');
      var tags = el.data('flockler-tags');
      if (!webhookID && !siteID && !sectionID && !tags) {
        if (window.console && window.console.error) {
          window.console.error('Load more failed! No webhook, site or section ID found.');
        }
        return;
      }

      var offset = el.find('.flockler-wall-item').length;

      var maxID = el.data('initial-max-post-id');

      var masonryOptions = {
        itemSelector: '.flockler-wall-item'
      };

      var itemWidth = el.data('flockler-item-width');
      if (pureIntegerString.test(itemWidth)) {
        // Pure number = fixed pixel-based width, center via CSS
        masonryOptions.isFitWidth = true;
        masonryOptions.columnWidth = parseInt(itemWidth);
      } else if (percentageString.test(itemWidth)) {
        // Percentage = fixed percentage width (values should be of the form 1/x * 100% for best results)
        var percentageWidth = parseFloat(itemWidth. substr(0, itemWidth.length - 1));
        var maxColumns = Math.floor(100 / percentageWidth);

        var gutterWidth = (100 - (maxColumns * percentageWidth)) / 2;
        el.find('.flockler-wall__items').css({
          paddingLeft: gutterWidth + '%',
          paddingRight: gutterWidth + '%'
        });

        masonryOptions.isFitWidth = false;
      } else if (itemWidth === 'css-percentage') {
        // Special setting for complex CSS-based rules, such as multiple different widths for certain page widths
        // using @media queries, which require the fit width setting be turned off
        masonryOptions.isFitWidth = false;
      } else if (itemWidth === 'css-fixed') {
        // Special setting for complex CSS-based rules, such as multiple different widths for certain page widths
        // using @media queries, which require the fit width setting be turned off
        masonryOptions.isFitWidth = true;
      } else {
        // Last fallback: original behavior. Use default width of 346px
        masonryOptions.isFitWidth = true;
        masonryOptions.columnWidth = 346;
      }

      var endpoint = ajaxData.ajax_url +
          '?count=' + itemCount +
          '&offset=' + offset +
          '&width=' + itemWidth;

      if (webhookID) {
        endpoint = endpoint + '&hook_id=' + webhookID;
      }

      if (siteID) {
        endpoint = endpoint + '&site_id=' + siteID;
      }

      if (sectionID) {
        endpoint = endpoint + '&section_id=' + sectionID;
      }

      if (tags) {
        endpoint = endpoint + '&tags=' + tags;
      }

      // Initialize Masonry on the element
      el.msnry = new Masonry(el.find('.flockler-wall__items')[0], masonryOptions);
      updateItemHeightsOnImageLoad(el);

      var loadPosts = function(success_cb, timeout_cb) {
        success_cb = (success_cb instanceof Function) ? success_cb : function(){};

        $jsonp.send(endpoint, {
          onSuccess: function(json) {
            el.find('.flockler-wall__items').append(json.articles.join(''));
            updateItemHeightsOnImageLoad(el);

            // Load the newly added items to Masonry and recalculate the layout
            el.msnry.reloadItems();
            el.msnry.layout();

            // Copy over the new endpoint from the response
            endpoint = json.pagination.older;

            // If the endpoint was nulled or the response had fewer posts than was requested, hide
            // the button for more posts
            if (endpoint === null) {
              el.find('.flockler-wall__load-more-btn').hide();
            }

            (success_cb)();
          },
          onTimeout: timeout_cb
        });
      };

      // Load more posts when the respective button is clicked on
      el.find('.flockler-wall__load-more-btn').click(function(event) {
        var self = $(this);

        if (self.hasClass('loading')) {
          return;
        }

        self.addClass('loading');

        var removeLoadingClass = function() {
          self.removeClass('loading');
        };

        loadPosts(removeLoadingClass, removeLoadingClass);
      });
    });

    // Resize Facebook attachment images
    $('.flockler-article__fb-attachment__link__cover').each(function (i, image) {
      // var $fig = $(this).parent();
      // var $body = $fig.parent().find('.flockler-article__fb-attachment__link__body');
      var $cover = $(this);
      var $img = $cover.find('img');
      var img = $img[0];

      imagesLoaded(img, function() {
        if (img.width > 460) {
          $cover.addClass('flockler-article__fb-attachment__link__cover--large');
        } else {
          $cover.addClass('flockler-article__fb-attachment__link__cover--small');
        }
      });
    });

    // attach an event listener for the video play icons
    on('click', '.flockler-wall-item__media__video-icon', function() {
      var el = $(this);

      if (el.hasClass('html5-embed')) {
        // HTML5 embed: add a video tag into the container
        el.parent().find('img').addClass('hide');
        var post_container = el.parent().eq(0).append('<video controls><source type="video/mp4" src="'
            + el.data('videosrc') + '"></source></video>');

        // Manually start the video, jQuery starts a duplicate video in the background if autoplay is enabled
        post_container.find('video')[0].play();
      } else if (el.hasClass('embed')) {
        // Other embeds: let the oembed library handle that
        el.parent().find('img').addClass('hide');
        el.oembed(el.data('videosrc'), {
          vimeo: { autoplay: true },
          youtube: { autoplay: 1 },
          afterEmbed: function() {
            if (window.FB && window.FB.XFBML) {
              // Force Facebook SDK to parse all of its (video) elements again;
              // otherwise any that were added since the SDK itself was loaded will fail to load properly
              FB.XFBML.parse();
            }
          }
        });
      }
    });
  });
})();
