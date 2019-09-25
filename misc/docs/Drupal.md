# Drupal

## A plan...

1. In my local installation, replicate the Voscur Calendar view and Upcoming Events block (is it a block?) and install the Bootstrap Business theme
3. get Civi components running on local installation - nb in the Voscur installation there are (1) components installed and integrated with Drupal (right?) (2) components installed and *not* integrated with Drupal (CiviEvent, CiviMember, and more) (3) components not installed (actually, no - none that we will need in the foreseeable)
3. find out about Voscur SSL certificate - what type (EV presumably), from who, how much...
4. [investigate using Civi with Worpress]



## Drupal concepts

So you create some content types and some items of content (in order to create dynamic content). To be able to show any of this, you need to create Views. A View is basically a database query - in the UI you specify what will be displayed, and how it will be filtered and sorted etc. You can create a Page or a Block from a View. The benefit of creating a Block is that you can deploy it here and there (in Regions) whereas a Page is just a page. NB you can create any number of Pages and Blocks from a View, and in those you can specify different filters, different fields (when you make local changes the sections will be shown in italics - I think), but sometimes you might be better off creating a new View. The more bespoke pages you create based on a View, the more confusing things become, and there is always the chance you will screw things up by applying changes to the whole View and not just the current Page or Block (referred to generically as 'display', as in 'Apply (all displays)' etc).

Attachments - an Attachment is yet another way to display View data. The use case in the Cookbook is for news items where say the first three are highlighted and the remainder listed in a different way. I'm not sure why you use an Attachment rather than a Block - ?

Filtering views - you determine *what* records are displayed by applying Filters (whereas Fields determine what is displayed from each record). Filters can be exposed in the external site (so this is equivalent to what I was doing all those years ago with Zend - very much so in fact as there are options for AND-ing or OR-ing etc etc).



## Drupal weirdness

Here's a weird and confusing thing that can happen... 

1. Log in and go to a web page, say Voscur home page, 
2. click Edit in the Drupal tabbed menu, 
3. click Manage Display, 
4. click Edit again

*...you are not in the same place as you were at step 2!*

...and furthermore, at no stage do you see the content that's in the home page. So what is going on here?

We have a content type Page. What we are looking at in step 4 is the UI page that defines that content type.

Note the Page content type is a mess. It has Title and Body fields, as standard for 'Basic Page' out of the box in Drupal 7, it also has some standard fields that come from some installed modules e.g. 'URL path settings', but it also has a whole load of locally created fields like Section, Topic/Keywords, Type, Visibility, Groups audience, and Image Credit. These seem to me like corruptions of the Page content type, but I'm prepared to be corrected...

What we are looking at in step 2 is the UI page that defines Home Page, which is an *instance* of the Page content type. So we see all the fields listed above, but NB they are all empty or unset except Title. So it's yet to be determined where the content of the home page comes from.

> In step 3, we have moved from the Home Page instance of Page and are now looking at the generic display setting for the Page content type, **not** the display settings for the Home Page.
>
> ...consequently, in step 4 we are looking at the first part of the definition of a content type called Page (its name and description, and other stuff under Publishing options, Display settings and so on).
>
> In step 2 we are looking at an implementation instance of Page, so we don't see the bit above, the overarching settings for Page, but the *fields* for this particular instance.
>
> The thing that makes this so confusing is that when you click on Manage Display it takes you to the Page content type blocks/regions definition. 
>
> What about the things that are visible in the Home Page? How do we find those?
>
> The image carousel can be found by clicking on its gear icon where it appears in the web site. You then get a choice between Edit View or Configure Block. From configure block, we learn that the block is called '*View: Front page slideshow*'. In the definition of that block we can see, under Pages tab at bottom of the page, that this should appear on 'Only the listed pages' \<front\>. But why isn't this block viewable from Manage Display anywhere??
>
> 



## Panels

To get this up and running you have to install/enable some modules/features. One is Page Manager - when that is installed you get a new entry in the Structure page 'Pages' and this takes you to the said Page Manager. From here the next step in the instructions is to 'Add custom page'. Note that Basic Page still exists as a content type and there is no new content type for Custom Page in the Content page.

When you create a Custom Page you select a Panel layout. Click through the next page and you get to a page with a 'map' of the panel layout from where you can configure individual panels. NB these panels are in the content area of the page, nothing to do with navigation or other page furniture.

The thing that is significant about this way of creating content is that it is content *outside of a node*. That's generally not a great idea as it means it can't be re-used (or easily updated). So it is usually preferable to add a node. The Cookbook describes creating a Basic Page to add to the page - more confusion. Anyway...

Oh - this is beginning to explain why in for e.g. the Voscur Home Page you never get the tabbed View|Edit menu - in the Custom Page of Panels created above we see te same thing, but in the Basic Page just created, there's the View|Edit menu... If you add this newly added Basic Page to a Panel you still don't get the View|Edit menu, but you get different options from the gear icon (instead of just Edit Panel, you get Edit|Delete). But... you also don't View|Edit in my Organisations by Type page but that doesn't have panels...

Next, adding a View to a Panel... Fairly straightforward, but there is a bit of weirdness in how gear icons appear in the rendered page. If you add the view as 'Page' (my Organisations view at least) you get a full list of organisations as you would expect, but there is no gear icon for the view as a whole, just individual gear icons for each org. If I add the Organisations view as a block, I'm just getting one org, and a title Organisations offering Advice Services for fuck's sake. Ah that's because the generic Block version of this view has service type set to Advice Services (for some reason).





## Core installation & modules

There is very little functionality in a core installation. For example, in Drupal Cookbook you are advised to install a new module from within Drupal using the 'Install new module' link, but omits to mention you first have to *enable* another module - Update Manager - to make the 'Install new module' link appear. (So this module is part of a core install but is disabled by default).

NB you can install a module by downloading, unzipping and placing in /modules/ folder in code base.

You can also use the Drush command line tool.

NB there are two modules folders, /modules/ and /sites/all/modules/. The first is for core modules.

## Cron & updates

After enabling Update Manager you get a new tab *Update* in the Modules page, and you are liable to see this message:

> No update information available. [Run cron](http://localhost/drupal7/admin/reports/status/run-cron?destination=admin/modules) or [check manually](http://localhost/drupal7/admin/reports/updates/check?destination=admin/modules).

So you have the option of running a manual check for module updates or setting up 'Cron' to perform automated updates.

The Modules page also includes a link to Available Updates - this is a report that shows the status of the Drupal Core and any other modules. You are advised to check this regularly. There's also an 'update script' which you are advised to run after installing a new module, but that's a bit more involved [is this accessible in the Voscur install?].

Cron is used by lots of other things in Drupal e.g. maintaining search index. By default Cron is run every 3 hours but you could change that from Configuration->System. This is a single global setting which I guess is the minimum unit of automation?? That is you can set things to run less frequently (like the automated reports I did for Voscur) but not more frequently. To be confirmed...

## Cache

Configuration->DEVELOPMENT->Performance

...go here to 'clear all caches' after major changes

## Blocks

A block is a 'layout element'. But it's not to be confused with a Region.

Each Drupal theme has a quantity of predefined Regions - things like Header, Featured, Highlighted, Sidebar First, Sidebar Second, etc etc. You can view where these are for the current theme via the link in the Blocks management page 'Demonstrate block regions (theme name)'. [but I can't see this in the Voscur install... that's because it's in a block called System help and that block is not assigned to any region]

The Blocks management page lists these Regions with the Blocks contained within them. Rather confusingly, there's also a column for Region which contains a dropdown showing the current region, and you move the Block into another Region via this drop down. Not a great bit of interface design if you ask me. NB there's also a 'configure' link for each block which allows you to set Region *per theme* i.e. including themes other than the current default. So you must be careful that when you change theme you don't at the same time lose items of content (!).

Note re 'Disabled' section in the Drupal block listings page - these are (these *include*) blocks that can't appear in the current theme because they have been assigned to a content region that doesn't exist in the current theme (or a region of 'none'). That's super confusing because obvs you use your admin theme to edit the content of your externally facing theme, but then you have blocks that are 'disabled'. So what you have to know is when you are dealing with changes to the appearance of the external site, you must first select the theme that is used in the external site.

### Menu blocks

[Menus, without menu block - if you set parent item of a page to be some other page and not Main menu, then its menu link will at first just disappear. You need to install menu_block and then create a submenu to get them back. Weird]

These (menu blocks) become available when you install the menu_block module. A link 'add menu block' becomes available in the blocks management page.

The bare install includes *menus* but not *menu blocks*. What's that all about? This is like Views - a view can exist on its own and it can be made into a block. You can only *display* a menu by creating a menu block, right?

Starting level - 1st level means always visible, 2nd level will show only when you navigate to the level of the block that has the menu as its Parent item.  This is confusing. Easiest to understand using meaningful pages as per the cookbook example - About us (the parent page), Our mission, and Contact us (2nd level sub pages). When you first add these pages and set About us as the parent pages for Mission and Contact, the sub pages won't appear in navigation - except in the About us page, if you have created a Submenu block. By god this is confusing. The submenu block has 'Main menu' as its 'Menu' but this is not the tabbed menu that appears automatically in all Drupal pages. I don't understand where this menu comes from. This 'Main menu' doesn't appear anywhere in the site until you create the submenu. Rather it is the submenu we are seeing and Main menu doesn't by default appear anywhere. Setting Main menu as the Menu means this is the menu from which to take the menu links to build this submenu.

I think it's easier to understand the concept of starting level by thinking of menus *other than a main navigation menu*. Then it makes a bit more sense to have a 'starting level' other than '1st'. 

...however, if we try to create a main navigation with drop down menus, it will only really work if we put it in a sidebar because it renders as a vertical indented list. To have a horizontal menu with drop downs we need Superfish or Nice Menus or whatever. The book uses Superfish, but I'm going with Nice, because that's what is used in Voscur site.

#### Superfish/Nice Menus

First install Libraries module - "The common denominator for all Drupal modules/profiles/themes that integrate with external libraries." https://www.drupal.org/project/libraries

Then install the Superfish module (and remember to enable it and the Libraries one). Now you also have to manually put the superfish folder in sites/all/libraries/. Weird. 

So you create a superfish (or nice menus) menu and then *supress* the default automatic menu via Structure->Menus->settings tab: Source for the Main Links = No Main links. But this is not how it's done in Voscur site (maybe Nice menus is different?)

Had jquery issues with Superfish which I fixed using jquery update module and updating jquery version. But then there were css problems. So I used Nice Menus instead. Also jquery problems but used configuration->development->jquery update to roll back the jquery version (you can choose version).

Recap on menus: install menu module; this will create some menu blocks e.g. superfish-1 - put one of these in a region. Use menu settings to supress auto main menu etc. First you have to have a submenu block?? No, that's a red herring. I deleted my submenu and the Nice Menus drop downs still work. confusing. I suppose I set the hierarchy in Main Menu during creation of submenu, then used that in Superfish/Nice menus. Need to revisit this...

### Conditional display of blocks

...under Visibility settings, you can make display of the block conditional on a variety of things - specific pages, logged in user role etc.

There's a module Context which allows you to extend this conditionality much further [not used in Voscur]

## Views

A view is a representation of a database query. So you would certainly create a View to make lists of events, products, contacts, etc. and hence the process of creating a View includes options for pagination. [To display a view you might put it in a block, right? You can't put a view - or anything - on a page without it being in a block, right?]

Views are not included in Drupal core. You have to install the Views module, and also the Chaos Tools module.

When you create a View it will appear in your list of blocks in the block management page [if you checked the Create a block box along the way]. To begin with it will be in the Disabled section, because you haven't assigned it to a region (the listing of Blocks is per Region). As above with menus, you need to assign the block to a region for it to appear in your site. And as with menus, views can exist on their own *or* become a Block (after installation of Menu block module, in the case of menus).

Displays of views: when you first create a view, you get the option of making a page, or a block or both. You can subsequently add further pages/blocks and these appear in the View editing screen. There can be loads, e.g. the 'Calendar' view in Voscur Drupal has nearly 20... (including for e.g. Upcoming Events, Volunteer week 2016, Recently added, Funding feed)

### Relationships in views

I was able to create a new taxonomy term 'training level' with an associated icon image, then associate that with training events i.e. so you can specify 'level' (beginner/intermediate/advanced), then in the events view pull in the icon for the given level. HOWEVER, if you make it so the training event can have more than one level, you get duplicates. Not surprising I suppose... Perhaps this is a use for 'attachments'?

A bit more on this: if I *don't* specify a relationship then I can select Training Level as a field and I'll get a text list (links) separated by commas. I need to specify a relationship to be able to access the icon that is associated with the training level taxonomy item, but as soon as I do that I will get multiple rows. 

The simple task of displaying an icon for level is ludicrously difficult. Following this https://www.drupal.org/node/1224916 (note the ridiculous fact that you have to add the relationship to level TWICE once for level and once for the 'entity reference'. What fucking shit) I'm nearly there but still can't get rid of the header above the icon. Tempting to use CSS...

Someone else had the same idea (using CSS): https://www.drupal.org/forum/support/theme-development/2018-08-24/display-images-for-taxonomy-terms-in-term-reference-field. Note they say to 'use a custom block', not to edit CSS directly...



## Libraries

To install (for example) an HTML editor, you have to install a module (WYSIWYG) and then a library for the specific editor you want to use. To install a library you need to download it, extract it, and then place it in /sites/all/libraries/. 

### HTML editors

NB before you install the library, going to Configuration->content Authoring->Wysiwyg profiles takes you to a page with a list of HTML editor library options but once you have installed one, this page no  longer appears and 'wysiwyg profiles' takes you to a config page where you can assign you editor to an 'editor profile' - filtered html/full html/plain text - and so on and on. No wait - the HTML editors list is still there but hidden under 'installation instructions'. Anyway, you can go into great complication about who gets what editor, for editing what, and where. There's also a ton of configuration of the editor that you can do e.g. rules for sanitising pasted text, and what styles to show - the default is 'Use theme CSS'...

You can enable the insert image button but out of the box it only works partially - you can see the image in the editor but it doesn't appear in the page. You need to add more modules to enable an image to be uploaded to the server - one that enables file uploads and one that makes that available to the HTML editor. And then do a bunch of config. And the end result (following instructions in Cookbook) is horrendously clunky (but is the browser's file browser at the end). And is the same on the Voscur site - but interestingly in the navigation you get root>editors and root>images. Editors is selected first by default. There's images in both folders. I really want to know what Polly for e.g. knows about this and whether she uses it... (and everything else herein for that matter - theme regions, blocks, html editor full v filtered html etc etc etc)

## Content Editing

### Friendly urls

Easy - just enter 'URL alias' under URL path settings. Voscur has the pathauto module installed which automatically creates friendly urls.

### Content types

Added content type Animal, made a View to display them, assigned that to a block, created a page Animals, configured the View/Block so that it only appears in the animals page.

When you start adding animals, they have equal status in the list of content as pages and any other content type - so the page that lists the animals has equal status in the list as the individual animals. And this is just a great big list, so it must get huge in a mature site... [confirmed with a look at the Voscur site]. Also, there's a lot of scope for accidentally/temporarily adding animals/the list of animals to pages and menus they don't belong with.

I can see how this stuff is more powerful than Wordpress - because Wordpress is just about displaying web pages - but if you are using it with Civi all your non-web page content will be in the Civi database, so all this extra complication is really unnecessary - ?

You can of course filter the Content list...

Added content type Organisation - plan is to make a mock up of Voscur VCSE Directory. Will do this in Drupal for now, and maybe later find out how you link it through into the Civi object... I'll also need to add content types or taxonomies for categorising orgs, which will be interesting...

### Revisions tab

This becomes visible (in addition to VIEW and EDIT) when you add some 'Revision information'. In the Cookbook they show a Log tab, but I can't make that appear...

## Themes

### Adding a region

This requires back-end access - because (only because?) it involves **creating a sub-theme**. Instructions are in the Cookbook. Basically you create a new folder in themes, copy in some of the files from Bartik, make appropriate changes - including identifying Bartik as the parent theme (so most of it is inherited) - then the new sub-theme will appear in the Drupal interface. 

Following the Cookbook instructions, the new region went into the template directly below the header. Now we put the Nice Menu nav in there and hey presto.

NB the new region doesn't appear in order of where it appears in the page - perhaps in order of where it appears in the .info file?

### Down a rabbit hole...

I was trying to apply a style to each item listed in a View. In order to do that you need a wrapper div and I *thought* there was no such div in my theme (Bartik, sub-theme of). So I went down a rabbit hole of how you add custom layout to a theme (sub-theme). In fact it turned out that there was already such a div, but I just missed it (because the css wasn't behaving as I expected it to). But it's worth briefly documenting what I got into. You *can* change the way that your theme renders a view listing by making a local copy of \sites\all\modules\views\theme, in the root folder of your theme, and then messing around with it. NB there are various other files you can dump in there in a similar way and for similar reasons, e.g. if you wanted to create a preprocessor hook (what this means I think is to be able to get at the rows array before it is turned into a big string of html, when you want to add tags, classes etc). NB you always need to refresh the cache when you make these kinds of changes - not every time you make a change within a file, but when you move or rename files that are being included.

I might want to revisit this to move the 'displaying 1 - x of y' message.

## Some modules...

### Pathauto

Adds a checkbox for automatically generateing a friendly url (instead of /node/123)

### Admin menu

Tweaks for admin interface, including the ability to collapse the modules list. NB this is what makes the menu text small, and creates not particularly helpful drop down admin menus.



## Voscur-specific

The Blocks are a fucking mess. I can't believe what a state this is in really. What makes it additionally confusing is the block management page shows all the blocks used anywhere in the site, and in the case of Voscur, all the blocks *that have ever been used in the site*. So for e.g. there is a region called 'highlighted' which contains amongst other blocks 'volunteer week 2016 logo'. When you look at the config for that block it is set to appear only in one (obviously outdated) page.

I finally found a block that relates to something I can see in the site: 'footer 2 block'... but if I add System help block to that region it doesn't show - perhaps that's a precautionary setting i.e. can never be viewed in a non-admin theme. When I added it to Content it does now show in the admin theme. Hallelujah. Note that Seven, the current admin theme, has just two regions, Content and Help. Bootstrap Business has a lot more, but not as many as are shown in the block management page. For e.g. 'Pre Header First'. But these appear in the *Region settings* drop down in block->configure, so wtf? Ok, only regions that are always shown are shown in 'Demonstrate block regions'. Any that are conditional are not shown. I think (based on a sketchy forum thread). Yes: going to the Bootstrap Business code base and viewing page.tpl.php I can see stuff like:

```php
<?php if ($page['pre_header_first'] || $page['pre_header_second'] || $page['pre_header_third']) :?>
<!-- #pre-header -->
<div id="pre-header" class="clearfix">
```

...but further down the page the div with id="header" is not conditional.

Need to review use of wysiwyg in Voscur site.

Need to ask Polly (and others) about what features they actually use and understand (eg use of wysiwyg, inserting images)

Changing colour of admin interface. Admin uses Seven. The out-of-box appearance config options for Seven are v limited. If you want to change colours, it seems you have to install a module 'color'. No - Bartik is what I'm using in my local install, Voscur uses 'Seven'. It seems like you just can't change the colours of Seven - except by editing the theme's css directly which we can't do. [I wonder if there has been some customisation done in the past, specifically the size of the menu font?]. Actually my admin theme *is* Seven. Bartik is the public theme. It's pretty unclear in Drupal how this lot works - in the theme settings dialogue there is no prominence given to the themes that you are actually using.

It does appear to me that the main navigation menu is completely outside of Drupal control. Inspecting the css, it doesn't appear to be within any region. That's pretty stupid if true. Maybe there's some bit of Circle code that generates this menu, but even if there is what's the bloody point if we don't have any control or acces over it? Are we supposed to pay them any time there's a change to a menu item? What's the point of a CMS again??

...in fact there's very little sign of correlation between the theme regions and *anything* (on the front page at least) in the html...