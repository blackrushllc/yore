# YoreDatabase
A Yore Plugin

+ Provides a global database connection for Php everywhere
+ Includes a class library of Php functions for doing database stuff
+ Adds Phats for displaying database stuff like @browse(), @select() and @sproc()


CONFIGURATION: 
+ You provide connection info OR
+ You provide a Blackrush ID
+ If the BID is valid, then a it connects to our database cloud service
+ On first connect, if the database does not already exist it gets created.
+ If you need additional databases then use additional BIDs
+ On first connnect, the fundamental Yore tables are created (even if it's a seconary BID, you can just remove them if you want)
+ On connect, an connection object is returned which contains a $conn that you can use directly
+ The object also has an info block with the connection info you can use to connect with Heidi or some other 3rd party client
+ You can configure the object to preload some tables so you don't have to query them
+ The preloaded tables can also be on demand rather than preloaded, invisibly, for performance
+ Preloaded tables and regular tables use some kind of Model declaration which is also used to simplified CRUD and stuff if they've been set up
+ Models are just config data but make a Model class to use them
+ Remember, the idea is that the web/app master isn't going to do any Php coding unless they create a custom module
+ 