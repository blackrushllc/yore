# Navbar

Use this module to generate the Navbar currently seen in the theme folder
using markdown and Fred, and using the Module class in thie module to do the
Php things based on the static pages we have set up and the Modules we have
active.

Use the page json to control of a page appears in the navbar Top, as a Dropdown
item, or on a submenu.  The Navbar module can interpret this however it wants and
can even have it's own requirements for data elements to be in the page jason.

For example, if you have a custom Navbar module that has a unique structure,
you can require users to add a new custom element to their page json that isn't
normally required or known to Yore.

Maybe we can have modules expose some kind of information that the Page Edit
function can see and add the new option to the json editor section of the 
Page Editor, like:
```
public $json = 
  [
    [ 'navbar', 'Show page in Navigation Bar', 'bool', false ],
    [ 'navmnu', 'Menu name for Navbar (false for Top)', 'mixed', false ],
  ];
```

So of the module has a public $json variable then the page editor knows to add
the option on the page json properties form.

The module can also use this variable to determine default settings for pages
that have not had these parameters added to their page json.

The page editor would also point out that these settings are coming from this
module and will only apply of this module is active.  If you deactivate a 
module after settings have been added to a page, the database should retain these
settings even though they are irrelevent and not editable.

Or maybe have 3 states for a module, active, inactive, and disabled. So you can
have an inactive module that still lets you use it's back-end properties (like
the public $json) but the module doesn't react to any front end activity like
Fred functions or routes, etc.
