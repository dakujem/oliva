# A refactor of dakujem/oliva-tree

... after 8+ years

---

tree generics
- node visitor (base for possible future filter, comparator, etc.)
    - no need for visitors, visitors may be implemented in userland
    - iterators are enough, but thre need to be 4 of them: DFS preorder, inorder, postorder; and BFS

builder:
- only native callables for tree building
- use strategy instead of traits
- use shadowing - internal nodes for building, then return the shadow tree

omit:
- tree classes (not needed)
    - replace with a tree manipulator
- comparator (too complex for now)

refactor
- traits into classes
- ditch all PHP 5 constructs
- fully typed

add
- address object/interface
