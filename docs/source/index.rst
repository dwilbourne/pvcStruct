
.. toctree::
   :hidden:

   install
   usage

==================
pvcStruct Overview
==================

The pvcStruct package provides generic data structures:

* Lists (ordered and unordered)
* Tree (leaves ordered or unordered)
* Range, which allows you to specify discontiguous ranges of elements.  It was inspired by the Print dialog box where
you can specify page numbers to print, e.g. something like this: 1, 3, 5-9, 12-16.


Design Points
#############

* Lists and Trees are used in a wide variety of use cases.  These structures provide the basic mechanics of list and
tree creation, list element and tree node creation, movement through and manipulation of these structures.

* No dependencies outside of a few supporting pvc libraries (interfaces, err)



