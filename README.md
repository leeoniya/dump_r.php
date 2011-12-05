# dump_r() #
_a cleaner, leaner mix of print_r() and var_dump() for the browser - with HTML5/CSS3/JS at your disposal, you deserve better_

__see a [screenshot](https://github.com/leeoniya/dump_r.php/raw/master/example.png) for a taste of the default output and css syling__

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

i've tried and noted quite a few alternatives here: http://www.codingforums.com/showthread.php?t=230042. but none were to my liking, hence this project.