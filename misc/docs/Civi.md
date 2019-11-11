# Civi

## Database import aggro

...putting this at the top because it was such a load of aggro...

Doing a phpmyadmin export/import the inmport failed with a foreign key error. I wasted loads of time trying to figure this out, in the end I just removed the offending line from the import script, then ran it separately via phpmyadmin. 

Source of the problem:

```sql
--
-- Constraints for table `civicrm_country`
--
ALTER TABLE `civicrm_country`
  ADD CONSTRAINT `FK_civicrm_country_address_format_id` FOREIGN KEY (`address_format_id`) REFERENCES `civicrm_address_format` (`id`),
  ADD CONSTRAINT `FK_civicrm_country_region_id` FOREIGN KEY (`region_id`) REFERENCES `civicrm_worldregion` (`id`);
```

Changed to:

```sql
--
-- Constraints for table `civicrm_country`
--
ALTER TABLE `civicrm_country`
  ADD CONSTRAINT `FK_civicrm_country_address_format_id` FOREIGN KEY (`address_format_id`) REFERENCES `civicrm_address_format` (`id`);
```

Then separately ran:

```sql
ALTER TABLE `civicrm_country`
  ADD CONSTRAINT `FK_civicrm_country_address_format_id` FOREIGN KEY (`address_format_id`) REFERENCES `civicrm_address_format` (`id`);
```





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

### Listing events in external site

In Civi, from Manage Events, you can click on the HTML listing icon to get a page listing forthcoming events, with this url http://localhost/drupal7/civicrm/event/ical?reset=1&list=1&html=1

You also get a block in Drupal, CiviCRM Upcoming Events (Disabled)

**Experiment 1** - create a page in Drupal and use the CiviCRM Upcoming Events block. First create a Basic Page; then get rid of some extraneous blocks from the left sidebar (in Bartik Extra) - this is easiest done from the page itself using gear icon->configure block. Note that the url of this new page is /content/events-civi, so question: why the 'content'? I don't get this in my previous Training page - presumably because I created that from a View. I can change the URL easily enough (I think) but this is typically confusing stuff from Drupal. If I'm going to change it best to do it sooner rather than later because I use it to control what blocks appear within it - per block - so if I change it later I'll have to hunt down all usages and change the visibility settings.

 So this works pretty well. Because I created it as a Basic Page, there's a bit of text at the top that could be useful for some intro to the listing - or it could be a pain in the arse. What we really need is a way of replicating the filtering that we have in the Drupal version. The Civi events listing is a table, which you can sort on headers. If the table included Event Category or whatever then that would be a sort of filter, but not really what we want. I guess there may be options you can set for this...

[aside: this directly civi generated page has come with a printer-friendly link - who the fuck wants that?]

**Experiment 2** - use the Civi events block to create a view?? No, that's not how it works - you create a block, or page, *from* a view. A Drupal view remember is a database query - I guess the question is can that query be of the civi database? Yes - https://docs.civicrm.org/sysadmin/en/latest/integration/drupal/views/ "CiviCRM integrates with Drupal Views and allows CiviCRM data to be shown on your website". so I think this means we start by creating a drupal view, not by using the Civi events block...

Yep. Following the instructions in the above link I have achieved Civi/Drupal integration and can now create a View that pulls data from Civi.

### Event categories

Ok so that went pretty well - there's three different ways to display events from Civi in the outward facing (Drupal) site - the HTML listing from Civi, the Civi upcoming events block, or (best) integrating Civi and Drupal and using a view. But, for all of these I can't yet replicate what I have in Drupal, i.e. training events that can be filtered on category. In the Drupal-only version I've got filters on two categories - category (the VCSE Academy category) and level. Note that in civi the out-of-the-box event category is already being used to specify Voscur training. So, I will need to (1) create two custom categories in Civi (2) hope that I can use these in my Drupal view.

**Note** re categorisation (Drupal taxonomy) in Voscur - need to properly revisit this but remember the other day David was talking about the categorisation of events in the current site being based on a sort of global categorisation of Voscur stuff that they wanted to implement. I was reminded of this in the process of adding custom data for categorising events, because after you specify that your custom data is for events, the next question is which events it should apply to (because I've defined two event types - Voscur Training and 'other'). Basically, whilst I understand the thinking behind the Voscur global categories, I think it was misapplied.

It seems like custom data is implemented in a v sensible and thorough way - new tables are created, including link tables for many to many (or maybe they just do that regardless).



This is all going pretty well. 

Snagging:

- conditional display of address elements i.e. so that if some are not present we don't have unwanted spaces/commas. There are modules for this but none seem particularly well used, so try this: https://www.drupal.org/docs/7/howtos/build-a-simple-conditional-field-in-views-7x-3x - done
- make individual event page usable and consistent with external site, with link to registration form
  - the link created automatically is http://localhost/drupal7/civicrm/event/info%3Fid%3D1%26reset%3D1 which goes to a CiviCRM styled page, but the proper url (Drupal/Bartik styled) is http://localhost/drupal7/civicrm/event/info?id=1&reset=1, which is weird because they are the same just with html entities in the first one [NB I've configured the left hand menu blocks that were appearing in civi pages to display only on 'civicrm', i.e. have removed 'civicrm/*' which is what appears by default in civicrm blocks]. I fixed this by creating a rewrite rule on the event title and inserting the event id.
  - fix other issues with this page - get rid of the Voscur Training Category accordion, improve the appearance of the Register Now link/button, deal with the other mess at the bottom of the page
  - make it so that a logged in external user has a different view from admin i.e. doesn't see drupal/civi menus. This is a whole can of worms - see below re users & registration
  - make it so that a not logged in user has a different view as above - this should be somewhat less of a can of worms, and hence to do first
- training level
- display of time, from/to
- add an image https://forum.civicrm.org/index.php%3Ftopic=34667.0.html



### Anonymous user view of event

Currently I'm using this url to show an individual event http://localhost/drupal7/civicrm/event/info?id=1&reset=1, and I'm linking to that by re-writing the link on the event title in the event listing. But this is a civi url and (hence) I think I'm limited (?) in what I can do with it e.g. getting rid of the Voscur Training Category drop down, changing the Register Now link.

So I need to create a view like events-from-civi for individual events., right?

http://www.jackrabbithanna.com/articles/drupalcivicrm-integration - this is pretty unhelpful actually

Contextual Filters is the thing. I've made the civi events listing view construct a link from event titles to civi-event/[title] then created a single event view that has a 'contextual filter' that gets the event title from the url and finds an event based on that.

## User roles & registration









## Reports as dashlets





## Activities and cases

How is this implemented in Voscur (compared with default)?

In default, the Activities are sorted by id, within category i.e. the categories are Contact, civiEvent, CiviContribute, CiviMember, CiviCase and CiviCampaign and the activities are in order of id within each of these categories. There are a lot of activities in the CiviCase category. The first few are Open Case, follow Up, Change Case Type, Change Case Status, Change Case Start Date etc. 

NB there are only two Case Types, Housing Support and Adult Day Care Referral...

Interesting that there's no Close Case - because that is a Case *Status*. The first so many CiviCase Activities cannot be deleted or disabled - they are generic/core activities. There's a few at the end that are specific to medical/housing issues that can be deleted or disabled - I'm guessing there must be some example data built into Civi and that's why we have these activity types (and the case types above).

In Voscur it's all a bit of a mess. Lot's of activity types with 'x' prepended (the ones in the list above that can be deleted/disabled cannot be in Voscur - presumably some permissions issue).

There aren't many Case Types...

So let's look through some recent cases in the case dashboard...

The dashboard is a grid of Status against Case Type. For example, there are 174 Ongoing Enquiries, 676 Completed Enquiries, 163 Completed One Off Support, 23 Ongoing Boost Placement etc. etc. NB the last two statuses are 'Do not use (closed)' and 'do not use resolved'. What's that all about?

When you click through these figures and examine them in more depth, it's clear that many are just historical. I need to create a report of cases that shows when they were last updated etc. so I can get a handle on this data. You don't see that by default but you can see who the case manager is and whether that's a current member of staff, and sometimes a 'next scheduled' activity which may be years ago.

Case Reports in Voscur - these seem to be all historical junk. V old cases, and v short reports - so presumably using a status or relationship that isn't being used any more etc. This is true of Civi reports generally. We could probably delete *all* of them and start again.

Snapshot of cases matrix 16th October 2019:

|                        | For Review                                              | Ongoing                                                      | Urgent | Completed                                                   | Closed (incomplete          | DO NOT USE (Closed                             | DO NOT USE Resolved |
| ---------------------- | ------------------------------------------------------- | ------------------------------------------------------------ | ------ | ----------------------------------------------------------- | --------------------------- | ---------------------------------------------- | ------------------- |
| **Support Service**    | 4 - most recent is Dec 2018, all belong to Meera Pandya | 21 most recent June 2018, all belonging to people no longer working here | 0      | 87 - most recent Oct 2018, mostly belonging to ex-employees | 0                           | 61 - very old data, dates range from 2012-2015 | 2 - v old           |
| **Enquiry**            | 2 - **in active use**                                   | 175 - **in active use**                                      | 0      | 676 - **in active use** but lots of very old data           | 13 - most recent april 2018 | 16 - old data                                  | 1- v old            |
| **One Off Support**    | 0                                                       | 42 - no dates but all belong to ex-employees                 | 0      | 163 - no dates on any of these but all ex-employees         | 0                           | 12 - all ex-staff                              | 1- v old            |
| **Employment Service** | 0                                                       | 9 - v old and belonging to ex-employees only                 | 0      | 15 - v old cases                                            | 0                           | 1- 2014                                        | 0                   |
| **Boost Placement**    | 0                                                       | 23 - all belong to Jessica Langton and are old               | 0      | 5 - no dates, but ex-employees and low id numbers           | 0                           | 1- old                                         | 0                   |
| **Property**           | 0                                                       | 9 - most recent 2016                                         | 0      | 3 - all from 2015                                           | 0                           | 1- 2015                                        | 0                   |

NB it's quite difficult to assess these because the reports are sorted on organisation name. They can be re-ordered on dates, but many do not have a date (the id would be useful to order on).

Note that some statuses will by definition only have a transient use e.g. Urgent, so the fact there's no cases doesn't necessarily mean it should be got rid of. That could also apply to One Off Support - for this type it might be that we don't want to get rid of Ongoing, but just clear out these 42 old ones.

In fact nobody uses this - cases are created using a Drupal form,  https://www.voscur.org/record-enquiry-support-multi and managed using these other forms:  https://www.voscur.org/new-enquiry-cases-snapshot,  https://www.voscur.org/managed-enquiry-cases (not sure how much the last one is known about/used).

Looking at these forms gives a much clearer views of what statuses and activity types are in use. In fact **these forms are the key to understanding how Voscur manages Cases and Activities**. I should try and replicate them. [what they have done is hide the chaos in Civi by creating these forms, but note that there are points where the user is sent back into Civi so it's all a bit confused]. 

There is a training document on the shared drive 'Support inquiries and cases v.9.pdf' which details how these forms are used. It is *very* detailed, to the point of unreadability. This sort of documentation shouldn't be needed (replace with use of tooltips etc).

### Replicating support enquiry forms

Case statuses: For Review; Ongoing; Urgent; Completed; Closed (incomplete)

Type of enquiry: Funding; Signpost; Advice; Resource; Make Connectin; Specialist Support; Collaboration; Startup; Change; Crisis; Youth Sector Support Fund; 2020

When I am logged in to Drupal and view the above forms, I might see the Drupal tabs - I assume dev workers don't see these? How do I 'impersonate' another type of user?





## A note about managing database changes

currently have to manually export databases to into the repo but this is no good really - either:

1. change database location so they are included in repo
2. use a virtual machine



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