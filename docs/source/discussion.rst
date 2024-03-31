==========
Discussion
==========

The intent of these objects / structures is pretty self evident.  But there are a few things worth mentioning perhaps.

These structures were written using phpstan generics.  Using generics allows us to ensure that
all payloads within a collection or a tree are of the same type.  It also enforces the notion that all nodes in a
tree must be of the same type - you cannot mix different types of nodes in a tree.  Generics do not exist within PHP
natively, so you are encouraged to use phpstan (or psalm) to typecheck code that relies on these libraries.

There is an additional level of safety embodied in the use of a ValueValidator object (part of the PayloadTrait).
Type safety is good, but not perfect.  Imagine, for example, that you want your tree node to hold objects that are
not only the same type, but whose properties also conform to some business rule (say only objects whose color is red).
Generics cannot implement business rules like "red" or ensuring that each payload in the structure is unique, so
using a x tester may be helpful.


