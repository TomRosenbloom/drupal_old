# Civi

tags vs groups (vs custom fields vs membership type)

Relationships - nb contacts can be employees of orgs, not to be confused with inherited membership

## Events

Event types - an Option Group in Voscur Civi (default event types are mostly disabled - or edited)

How will we model the multi-level categorisation that we require, i.e. top level distinction between Voscur Training/external events, and then categories of Voscur training, level of Voscur training?

Use Campaigns?

Google API key - do we have a Google billing account already set up?

The Online Registration settings in the Voscur install need to be reviewed?

Added a Price Set - this feature isn't being used in the Voscur site. (Someone set it up back in 2015, but it was only used twice as far as I can see). How will this work with membership/discount codes? [NB added this, and other stuff to an Event Template...]

There is also the option of using Regular Fees, which is recommended for simplicity, but this doesn't appear to be used for Voscur events either.

[note all the clever things that Civi can do wrt handling waitlists e.g. if the event is moved to a larger venue then the next x number of people on the waitlist will be emailed. Lots of stuff like that...]

Profiles - Voscur has '\*Registration Info\*' (the asterisks are someone's attempt to separate current profiles from possibly defunct ones without actually getting rid of defunct ones) in the 'top of page' slot, but this is just one field 'Email Address (Contact)'. So this takes them off somewhere else if they are not registered? Would this work differently with inherited membership, i.e. you could select Organisation? NB Civi has a default 'Your Registration Info'

### Integration with Drupal

Once an event has been created, Civi offers a link for viewing the event, like http://localhost/drupal7/civicrm/event/info?reset=1&id=2. This is accessible from at least two places (copy and paste from info screen, via Configure drop down in Manage Events). 

Following on from previous work in Drupal, it's interesting when looking at this generated page to note a few things. There is the sidebar present that I excluded from the Drupal-only event pages that I made. Can't remember 100% how I got rid of this in those pages, but... This sidebar contains four blocks* which I can individually exclude from /civicrm/event/ pages, so I did that with the login box for starters - globally moved it into the header. The next block is interesting - it is a language selector and has appeared by magic, and is set to appear only in /civicrm/ pages. Pretty cool.

*four blocks when logged out. When logged in there's other stuff e.g. 'recent items', 'my contact dashboard', and more. This stuff is potentially useful, but needs configuring. 

NB these blocks are all 'CiviCRM... something'. I guess they came when I installed the Drupal Civi module

## Theme confusion

This could - probably should - go in the Drupal file, anyway...

I wanted to change the favicon per theme/subtheme so it can be different for Drupal admin/Civi admin, but it got super confusing.

In a normal theme or sub theme you can easily change the favicon just by putting the appropriate file in the root. There is also a per theme settings option in the UI where you can specify to not use the default favicon (i.e. the on in /misc/).

I set the Drupal public theme to bartik_extra, put a distinct icon in the root of the public theme. Easy.

The Drupal admin theme is Seven, so leave that with the default Drupal logo.

So I guess I need to create separate themes for Civi admin and Civi public...

This is just all over the fucking place - doesn't help that it is so,slow to refresh anything etc. and so hard to clear the cache. But it seems like when I change the civi admin thme to seven_civicrm_admin it is picking up Bartik - WHY!!!!

Next day... this remains a mystery - there must be some bug here. Even if I create themes seven_civicrm_admin and seven_civicrm_public, which inherit from Seven, when I specify via the Drupal UI that the Civi admin theme should be seven_civicrm_admin it instead becomes Bartik_extra (my sub theme of Bartik). If I set it to be just Seven then it's ok. So maybe the bug is you can't set the civi admin theme to be a subtheme?

https://civicrm.stackexchange.com/questions/22472/what-is-the-better-civicrm-administration-theme-on-drupal-to-apply-shoreditch-ex

The policy outlined in the above seems simple, but it remains stupidly confusing. If you click on 'settings' for a theme it takes you to a general theme settings page with tabs for each theme, and the one you clicked is NOT NECESSARILY ON TOP. When you set a theme for Drupal admin, be aware that when you log in from a public page YOU WILL STILL SEE THE PUBLIC THEME. That's reasonable in a way, what is confusing is that the public login is the same as the admin log in. That is fucking stupid.

When I set Adminimal as the Drupal admin theme, it is retaining the Bartik (drupal public theme) favicon - the choice of favicon is based on analysis of the url, so that's what you'd expect I suppose. I think, finally, that's the crux of it. You can't have a different icon for admin/public for Drupal, or by the same token, for Civi.

So:

- **you can't have a different favicon for admin/public in Drupal or in Civi**
- **you can't have a different favicon for Drupal/Civi if you want to use the same theme for public Civi as public Drupal (which you will)**

But, you can set a different them for admin/public in each case, and for each theme you have the option in Settings to not use the default favicon. It's bollocks really.