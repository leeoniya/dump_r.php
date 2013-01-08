## dump_r()
_a cleaner, leaner mix of print_r() and var_dump()_

__print_r() lacks__

  - types / lengths
  - adequate differentiation of bool/null/empty strings

__var_dump() lacks__

  - readability (forget about it)

__both lack__

  - value alignment
  - coloring
  - theming
  - uncluttered view without a sea of extra chars
  - tweaking of verbosity
  - interactive collapsing/expanding
  - adjustable recursion depth
  - ability to prevent useless expansion of empties
  - resource dumping
  - decomposing XML/JSON/SQL stings on-the-fly

--

__2013-01-08__  
PSR-0 rewrite

__2012-11-27__  
now installable through [Composer](http://getcomposer.org/)  
_require: "leeoniya/dump-r": "dev-master"_  
https://packagist.org/packages/leeoniya/dump-r

--

__default output and css syling__

![screenshot](https://github.com/leeoniya/dump_r.php/raw/master/dump_r_th.png "example.php")  
[view full size](https://github.com/leeoniya/dump_r.php/raw/master/dump_r.png)

i've tried and noted quite a few alternatives here: http://www.codingforums.com/showthread.php?t=230042. but none were to my liking, hence this project.