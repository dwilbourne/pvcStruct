
.. toctree::
   :hidden:

   install
   usage
   discussion

==================
pvcStruct Overview
==================

The pvcStruct package provides generic data structures:

* Collections (ordered and unordered)
* Trees (leaves ordered or unordered)
* Range, which is a simple structure containing a min and a max and which allows you to test a x to see if the
x falls within the range.  Combine this structure into a collection and you can produce a series of possibly
discontiguous ranges and easily determine whether a x falls within the ranges.


Design Points
#############

* Collections and Trees have a wide variety of use cases.  These objects provide the basic mechanics of creation
and navigation through the structures.

* No dependencies outside of a few supporting pvc libraries (interfaces, err)



