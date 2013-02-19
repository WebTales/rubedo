<style type="text/css">
html,body {font-family: '<?php echo $fontFamily_font_title_body; ?>', sans, serif; font-weight:<?php echo $fontStyle_font_title_body; ?>;
color:<?php echo $fontColor_font_title_body; ?>; font-size:<?php echo $fontSize_font_title_body; ?>}
h1, h1.post-title {font-family: '<?php echo $fontFamily_font_title_page; ?>', sans, serif; font-weight:<?php echo $fontStyle_font_title_page; ?>;
color:<?php echo $fontColor_font_title_page; ?>; font-size:<?php echo $fontSize_font_title_page; ?>; line-height:<?php echo $fontSize_font_title_page; ?>;}
h2,h2.post-title {font-family: '<?php echo $fontFamily_font_title_page2; ?>', sans, serif; font-weight:<?php echo $fontStyle_font_title_page2; ?>;
color:<?php echo $fontColor_font_title_page2; ?>; font-size:<?php echo $fontSize_font_title_page2; ?>;line-height:<?php echo $fontSize_font_title_page2; ?>}
h3{font-family: '<?php echo $fontFamily_font_title_page3; ?>', sans, serif; font-weight:<?php echo $fontStyle_font_title_page3; ?>;
color:<?php echo $fontColor_font_title_page3; ?>; font-size:<?php echo $fontSize_font_title_page3; ?>; line-height:<?php echo $fontSize_font_title_page3; ?>}
p{font-family: '<?php echo $fontFamily_font_content; ?>', sans, serif; font-weight:<?php echo $fontStyle_font_content; ?>;
color:<?php echo $fontColor_font_content; ?>; font-size:<?php echo $fontSize_font_content; ?>;}
.logotext a h1{font-family: '<?php echo $fontFamily_font_logo; ?>', sans, serif; font-weight:<?php echo $fontStyle_font_logo; ?>;
color:<?php echo $fontColor_font_logo; ?>; font-size:<?php echo $fontSize_font_logo; ?>}
.logotext a h3{font-family: '<?php echo $fontFamily_font_sub_logo; ?>', sans, serif; font-weight:<?php echo $fontStyle_font_sub_logo; ?>;
color:<?php echo $fontColor_font_sub_logo; ?>; font-size:<?php echo $fontSize_font_sub_logo; ?>}
.widget-title h3{font-family: '<?php echo $fontFamily_font_title_pages; ?>', sans, serif; font-weight:<?php echo $fontStyle_font_title_pages; ?>;
color:<?php echo $fontColor_font_title_pages; ?>; font-size:<?php echo $fontSize_font_title_pages; ?>;line-height:<?php echo $fontSize_font_title_pages;  ?>}
.tops{font-family: '<?php echo $fontFamily_font_title_menu; ?>', sans, serif;}
.breaking-title{<?php if (csc_option('csc_bg_break')) { echo 'background-color:'.csc_option('csc_bg_break'); } ?>}
a,
#change-small i,#change-small2 i,
.blog-meta .data .month,
.blog-meta .data .day,
.blog-meta .data .year,
#commentform label,
ul.price li.cost,
ul.price li.cost h3,
#container-ch .item-block-isotope .description,
.item-block-isotope .symbol,
.item-block-isotope .name ,
.item-block-isotope .summary p,
.sidebar_block h3,
.sidebar_block h4,.page-header h1,
.block-info .home-title,
a.description,
.promo-slogan h1 span,
nav ul.menu li a:hover, nav ul li.current-menu-item > a,nav ul li.current-menu-parent > a,nav ul li.current_page_parent > a,
.divider-strip h1,.divider-strip h3, h3.widget-title{color:<?php echo $fontColor_font_title_body; ?>;}
a:hover,#top a:hover,
#commentform label small,
#footers .tweets ul li a,.divider-strip.author h3 span,.menu-csc-side-navigation-container .menu li.current-menu-item a,.post-title.top a,.breadcrumbs_menu .current,ul.control-menu li a:hover,#wp-calendar tbody td a,.not-mes{color:<?php echo csc_option('csc_slogan_link_bg'); ?>;}
.menu-csc-side-navigation-container .menu li.current-menu-item a{ border-left-color:<?php echo csc_option('csc_slogan_link_bg'); ?>}
#top .info-text li{color:<?php echo csc_option('csc_top_inform_color'); ?>;}
#top .info-text li a{color:<?php echo csc_option('csc_top_inform_color_link'); ?>;}
.carousel-right:hover,
.item-block:hover > a.description,
ul.control-menu li a:hover,
#container-ch .item-block-isotope:hover > .description,
.item-block-isotope .zoomi:hover,.item-block-isotope .linki:hover,.item-block-isotope .info:hover,.open-block-acc.active,.open-block.active a,.cat-slider,#wp-calendar tbody td#today a{ background-color:<?php echo csc_option('csc_slogan_link_bg'); ?>;}
.widget-title h3{ background-color:<?php echo csc_option('csc_wid_tit_bg'); ?>;}
ul.control-menu li a,
.button:hover, .button:focus,
ul.control-menu li a,.button.blue,.port-info .port-info-back,a.all_break:hover{ background-color:<?php echo csc_option('csc_slogan_link_bg'); ?>; }
.top-bar{background:<?php echo csc_option('csc_topbar_bg'); ?> ;}
.top-bar{border-top:3px <?php echo csc_option('csc_header_bg'); ?> solid;}
#top-search .search-query.span4 { background-color:<?php echo csc_option('csc_top_search_bg'); ?>;}
nav ul.menu{background-color:<?php echo csc_option('csc_menu_bg'); ?>;}
nav ul.menu{ border-bottom:5px solid <?php echo csc_option('csc_menu_color_a_cur_bg'); ?>;}
i{color:<?php echo csc_option('csc_fontaw_bg'); ?>;}
#change-small .change-select,#change-small2 .change-select,ul.filter-change li a:hover,ul#portfolio-filter li a:hover,
ul.control-menu li a,
ul#portfolio-filter li a.currents,ul.filter-data li a.selected,
ul.price.best li.cost,
.progress-warning.progress-striped .bar,
.button.blue:hover{background-color:<?php echo csc_option('csc_element_bg'); ?>}
ul.control-menu li a,
ul.filter-data li a:hover{ background-color:<?php echo csc_option('csc_element_bg'); ?>;color: #f8f8f8;}
#change-small i,#change-small2 i{ color:#f8f8f8;}
.dropcap,
.button,
.hover-desc-bottom{background:<?php echo csc_option('csc_element_bg'); ?>;}
.theme-default2 .nivo-caption{background:<?php echo csc_option('csc_slidernav_bg'); ?>;}
.theme-default2 a.nivo-nextNav{ background-color:<?php echo csc_option('csc_slidernav_bg'); ?>}
.theme-default2 a.nivo-prevNav{ background-color:<?php echo csc_option('csc_slidernav_bg'); ?>}
nav ul.menu li a{font-family: '<?php echo $fontFamily_font_title_menu; ?>', sans, serif; font-weight:<?php echo $fontStyle_font_title_menu; ?>;
font-size:<?php echo $fontSize_font_title_menu; ?>;color:<?php echo $fontColor_font_title_menu; ?>;}
nav ul.menu li a:hover{color:<?php echo csc_option('csc_menu_color_hover'); ?>;background-color:<?php echo csc_option('csc_menu_color_hover_bg'); ?>}
nav ul li.current-menu-item > a,nav ul li.current-menu-parent > a,nav ul li.current_page_parent > a{color:<?php echo csc_option('csc_menu_color_a_cur'); ?>}
nav ul li.current-menu-item > a,nav ul li.current-menu-parent > a,nav ul li.current_page_parent > a,.flex-control-paging li a.flex-active,.flex-control-paging li a:hover,#magflexslider .flex-control-paging li a.flex-active,#magflexslider .flex-control-paging li a:hover  {background-color:<?php echo csc_option('csc_menu_color_a_cur_bg'); ?>}
nav ul.menu li ul li a{background:<?php echo csc_option('csc_menu_bg'); ?>;color:#f8f8f8;}
nav ul.menu li ul li a:hover{background:<?php echo csc_option('csc_menu_hover_bg'); ?>;color:#f8f8f8;}
nav ul.menu li ul li{background-color:<?php echo csc_option('csc_menu_hover_bg'); ?>;}
nav ul.menu li.sfHover > a{background-color:<?php echo csc_option('csc_menu_color_a_cur_bg'); ?>}
nav ul.menu li.sfHover > a:hover{background-color:<?php echo csc_option('csc_menu_color_hover_bg'); ?>;}
.nav-tabs > li.active > a,.nav-tabs > li.active > a:hover,.nav-tabs > li > a:hover  { border-top:<?php echo csc_option('csc_menu_color_a_cur_bg'); ?> 3px solid;}
.stripe-dots{ border-right-color:<?php echo csc_option('csc_menu_color_a_cur_bg'); ?>}
#share_post a.soc-follow.facebook { background-color:<?php echo csc_option('csc_socicon_bg_sh'); ?>}
#share_post a.soc-follow.twitter { background-color:<?php echo csc_option('csc_socicon_bg_sh'); ?>}
#share_post a.soc-follow.linkedin { background-color:<?php echo csc_option('csc_socicon_bg_sh'); ?>}
#share_post a.soc-follow.tumblr{ background-color:<?php echo csc_option('csc_socicon_bg_sh'); ?> }
#share_post a.soc-follow.google{ background-color:<?php echo csc_option('csc_socicon_bg_sh'); ?> }
a.soc-follow.dribbble { background-color:<?php echo csc_option('csc_socicon_bg'); ?> }
a.soc-follow.facebook { background-color:<?php echo csc_option('csc_socicon_bg'); ?>}
a.soc-follow.twitter { background-color:<?php echo csc_option('csc_socicon_bg'); ?>}
a.soc-follow.flickr { background-color:<?php echo csc_option('csc_socicon_bg'); ?> }
a.soc-follow.linkedin { background-color:<?php echo csc_option('csc_socicon_bg'); ?>}
a.soc-follow.vimeo{ background-color:<?php echo csc_option('csc_socicon_bg'); ?> }
a.soc-follow.google{ background-color:<?php echo csc_option('csc_socicon_bg'); ?> }
a.soc-follow.ember{ background-color:<?php echo csc_option('csc_socicon_bg'); ?> }
a.soc-follow.evernote{ background-color:<?php echo csc_option('csc_socicon_bg'); ?> }
a.soc-follow.forrst{ background-color:<?php echo csc_option('csc_socicon_bg'); ?> }
a.soc-follow.github{ background-color:<?php echo csc_option('csc_socicon_bg'); ?> }
a.soc-follow.last-fm{ background-color:<?php echo csc_option('csc_socicon_bg'); ?> }
a.soc-follow.paypal{ background-color:<?php echo csc_option('csc_socicon_bg'); ?> }
a.soc-follow.rss{ background-color:<?php echo csc_option('csc_socicon_bg'); ?> }
a.soc-follow.sharethis{ background-color:<?php echo csc_option('csc_socicon_bg'); ?> }
a.soc-follow.skype{ background-color:<?php echo csc_option('csc_socicon_bg'); ?> }
a.soc-follow.tumblr{ background-color:<?php echo csc_option('csc_socicon_bg'); ?> }
a.soc-follow.wordpress{ background-color:<?php echo csc_option('csc_socicon_bg'); ?> }
a.soc-follow.yahoo{ background-color:<?php echo csc_option('csc_socicon_bg'); ?> }
a.soc-follow.youtube{ background-color:<?php echo csc_option('csc_socicon_bg'); ?> }
a.soc-follow.zerply{ background-color:<?php echo csc_option('csc_socicon_bg'); ?> }
a.soc-follow.aim{ background-color:<?php echo csc_option('csc_socicon_bg'); ?> }
a.soc-follow.behance{ background-color:<?php echo csc_option('csc_socicon_bg'); ?> }
a.soc-follow.digg{ background-color:<?php echo csc_option('csc_socicon_bg'); ?> }
#top a.soc-follow.dribbble { background-color:<?php echo csc_option('csc_top_socicon_bg'); ?> }
#top a.soc-follow.facebook { background-color:<?php echo csc_option('csc_top_socicon_bg'); ?>}
#top a.soc-follow.twitter { background-color:<?php echo csc_option('csc_top_socicon_bg'); ?>}
#top a.soc-follow.flickr { background-color:<?php echo csc_option('csc_top_socicon_bg'); ?> }
#top a.soc-follow.linkedin { background-color:<?php echo csc_option('csc_top_socicon_bg'); ?> }
#top a.soc-follow.vimeo{ background-color:<?php echo csc_option('csc_top_socicon_bg'); ?> }
#top a.soc-follow.google{ background-color:<?php echo csc_option('csc_top_socicon_bg'); ?>}
#top a.soc-follow.ember{ background-color:<?php echo csc_option('csc_top_socicon_bg'); ?> }
#top a.soc-follow.evernote{ background-color:<?php echo csc_option('csc_top_socicon_bg'); ?> }
#top a.soc-follow.forrst{ background-color:<?php echo csc_option('csc_top_socicon_bg'); ?> }
#top a.soc-follow.github{ background-color:<?php echo csc_option('csc_top_socicon_bg'); ?>}
#top a.soc-follow.last-fm{ background-color:<?php echo csc_option('csc_top_socicon_bg'); ?> }
#top a.soc-follow.paypal{ background-color:<?php echo csc_option('csc_top_socicon_bg'); ?> }
#top a.soc-follow.rss{ background-color:<?php echo csc_option('csc_top_socicon_bg'); ?> }
#top a.soc-follow.sharethis{ background-color:<?php echo csc_option('csc_top_socicon_bg'); ?> }
#top a.soc-follow.skype{ background-color:<?php echo csc_option('csc_top_socicon_bg'); ?> }
#top a.soc-follow.tumblr{ background-color:<?php echo csc_option('csc_top_socicon_bg'); ?> }
#top a.soc-follow.wordpress{ background-color:<?php echo csc_option('csc_top_socicon_bg'); ?> }
#top a.soc-follow.yahoo{ background-color:<?php echo csc_option('csc_top_socicon_bg'); ?> }
#top a.soc-follow.youtube{ background-color:<?php echo csc_option('csc_top_socicon_bg'); ?> }
#top a.soc-follow.zerply{ background-color:<?php echo csc_option('csc_top_socicon_bg'); ?> }
#top a.soc-follow.aim{ background-color:<?php echo csc_option('csc_top_socicon_bg'); ?> }
#top a.soc-follow.behance{ background-color:<?php echo csc_option('csc_top_socicon_bg'); ?> }
#top a.soc-follow.digg{ background-color:<?php echo csc_option('csc_top_socicon_bg'); ?> }
.blog-meta .post-format span{background-color:<?php echo csc_option('csc_post_bg'); ?>;}
.post-format span,.post-format-s span{background-color:<?php echo csc_option('csc_post_bg'); ?>;}
.menu-t li a {color:#f8f8f8;}
</style>