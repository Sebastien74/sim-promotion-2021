import 'jquery.marquee';

/**
 *  Marquee Banner
 *
 *  @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
export default function (body, banners) {

    $(function () {

        /*!
         * Pause jQuery plugin v0.1
         */
        var $=jQuery,pauseId="jQuery.pause",uuid=1,oldAnimate=$.fn.animate,anims={};function now(){return(new Date).getTime()}$.fn.animate=function(n,t,e,u){var a=$.speed(t,e,u);return a.complete=a.old,this.each(function(){this[pauseId]||(this[pauseId]=uuid++);var t=$.extend({},a);oldAnimate.apply($(this),[n,$.extend({},t)]),anims[this[pauseId]]={run:!0,prop:n,opt:t,start:now(),done:0}})},$.fn.pause=function(){return this.each(function(){this[pauseId]||(this[pauseId]=uuid++);var n=anims[this[pauseId]];n&&n.run&&(n.done+=now()-n.start,n.done>n.opt.duration?delete anims[this[pauseId]]:($(this).stop(),n.run=!1))})},$.fn.resume=function(){return this.each(function(){this[pauseId]||(this[pauseId]=uuid++);var n=anims[this[pauseId]];n&&!n.run&&(n.opt.duration-=n.done,n.done=0,n.run=!0,n.start=now(),oldAnimate.apply($(this),[n.prop,$.extend({},n.opt)]))})};

        banners.each(function () {

            let banner = $(this)

            banner.marquee({
                /** duration in milliseconds of the marquee */
                duration: banner.data('interval'),
                /** true or false - Pause on hover */
                pauseOnHover: banner.data('pause'),
                /** gap in pixels between the tickers */
                gap: 0,
                /** time in milliseconds before the marquee will start animating */
                delayBeforeStart: 0,
                /** 'left', 'right', 'up', 'down' */
                direction: 'left',
                /** true or false - should the marquee be duplicated to show an effect of continues flow */
                duplicated: true
            });
        });
    });
}