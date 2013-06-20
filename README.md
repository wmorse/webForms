webForms
========

PHP scripts for making scheduled forms


2013-06-20
The code in this repository is a first time out in a public repository for my code. It contains 
all the failed attempts as I -WFM- learn to work with others. It is an application that was started 
in 2003, so, before the "git" phase there was approximately ten years of technology changes, potentially, reflected 
in the depths of this code.

The goal of this project is to clean up the existing codebase to be more readable, and usable, at least 
by me and my colleagues. 

If others find these scripts useful, that would be neat.

The very oldest scripts in this initial version were copied verbtim out of the tutorial 
book "MySQL/PHP Database Applications" by Jay Greenspan and Brad Bulger. Then the hacking began.
And the borrowing from a multitude of websites.

webForms -- and I'm sure the name will have to change because it doesn't sound cool and must be used somewhere else.

webForms: 

There are forms or "Surveys" that have one or more pages or "Sections".
Sections have "Questions"
Question can have "Answers".

Users: Some surveys are open to allow users to sign up an fill out the survey
      
Some surveys are closed except by invitation by an admin user.

Users can answer questions.

Surveys can be scheduled, and repeated.

Admin Users have access to alter Sections and Question on Surveys assigned to them. 
Admin Users can "see" Surveys completed and not yet completed.
Admin Users can download survey data in a table for a survey instance (a scheduled instance)


There are a bunch o' admin features that I would like to add to keep the admin users happy and out of my hair.

