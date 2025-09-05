# mapcycle
Mapcycle management page for SoF2

NB - the source code is provided as-is. No additional maintenance nor updates should be expected. I might share some adjustments which have been done for the version which is actively in use still today, but that's for another day.

PR's are welcome to the repo, but mind you this application for sure contains outdated modules.

Web based solution for handling/managing mapcycles. 

Whole application idea started long ago, when I built entity related functionality (uploads mostly).
After the whole idea was buried for a while, we had a need for it, so I quickly tried to put something functional together. 

Thanks to having quite a long time between the start of the project and doing most of the work, there's bound to be some code quality differences. There might also be some weird comments or commented out functionality or functions which was never used or which was just kept there in case I had to turn back or whatever.


Main functionality points:
1. Entity uploads - everyone who has a forum account can upload entities (it was connected with Xenforo forums).
2. Entity viewing - open for all
3. Entity votes - logged in users could vote for entities which they found fun.
4. Mapcycle creation - basically enabling the end-user to generate mappings between a mapcycle and an entity.
5. Mapcycle modification - ordering the entities, creating cvars for them 
6. Push-to-Live - based on a crontask, mapcycle switches can be automated and you have the possibility to have as many mapcycles as you wish. 

There are some parts which definitely need a bit of rework (for example, the FTP push currently spawns a connection for every file it uploads rather than reusing a single connection) etc.
