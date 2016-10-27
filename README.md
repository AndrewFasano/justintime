Just in Time
=============
300 point
Web
Andrew Fasano

TL;DR;
-------------------
This challenge can be solved by exploiting the time change from EDT to EST that will happen on Nov 6.

Prior to the time change, teams should find the exploit and be able to test payloads.
Teams need to create an account and verify it with a hidden debug parameter between 1AM EDT and 1AM EST (1 hour)
By sending the debug parameter, the account is disabled after that moment
After the time change, teams need to modify the VoterID they were given using the key provided by 


After the time change is over (2am EST 11/6), the challenge cannot be solved by the intended solution and will block most user input as to not waste time.


Bug 1: Local File Inclusion
---------------------------
	Use this to get source code of entire site:
	GET /inc.php?p=php://filter/convert.base64-encode/resource=index

	Use this bug to find the "d3bug" parameter on the verify page
	This should only allow access to .php files

BUG 2: Bad timezone logic
---------------------------
When a user creates a VotingID, they get back the following object:
` base64_encode(json.dumps([Serialize(VoterObject), sign(VoterObject, userkey), username, sign(username, private_server_key)]))`

When a VotingID is validated with the debug parameter, the user is given the userkey value (the private_server_key should remain private until the final step). When this happens, the account is marked as a "debug" account after that moment. This is done without timezones which is the bug.

If an account is marked as "debug" it prints an error before calling `unserialize` on the object.

A VotingID can be validated (with debug) on 11/6 1:30AM EDT and that will flag it as being invalid after 11/6 1:30. But 1:00 AM EST, happens after that. At 1:00AM, the account can be validated again (without debug) and it will call unserialize. At this point, a team can use the `uservalue` they have to sign a modified Voter object.

BUG 3: Unserialize user input
----------------------------
Once the server is unserializing Voter objects created by teams, it's possible for the teams to set the `verbose` property to True, and to point the `log` property to point to ./admin_password. Calling validate on this object will print the last line of the file that `log` points to, which will be the admin password.

The admin password can then be used to log into the management page where the file ./logs will be printed. One of the entries in this file is the flag.

Notes
-----
This challenge is hard to test. Currently working on a test script.

It's really important that the server this runs on is in the right timezone and will switch from EDT to EST automatically.

This challenge is designed to run in an east-coast time zone, but could be shifted to be another time zone to fit the challenge map. But then the window for exploitation would be evan later than 2AM EST which is pretty late for east-coast teams.

This still needs some testing and code cleanup

I picked 300 points becaues that seemed like a reasonable amount, but I'd be happy to move it up or down
