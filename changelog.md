
# Changelog

> 📖 back to [readme](readme.md)

Oliva follows semantic versioning.\
Please report any issues.


## v1.3.x

- `Node::addChild()` method (through the implementation in `MovableNodeTrait::addChild`) now allows adding the same node
  under the same key without having an effect, previously it would throw the `ChildKeyCollision` exception
- `Tree::link()` method now allows for turning off the duplicate node handling mechanism,
  which prevents adding the same child multiple times
  - used for performance optimization when linking multiple children in a sequence


## v1.2.x

- Added `TreeNodeTrait` and `MovableNodeTrait` that implement methods of `TreeNodeContract` and `MovableNodeContract`, respectively.
- Removed EOL PHP 8.0 support


## v1.1.x

- Added `Seed::chain` method for iterable collection chaining: the new method takes over `Seed::merged` which becomes its alias for backward compatibility.
- Deprecated `Seed::merged`, use `Seed::chain` instead.
- Fixed repeated calls to `ShadowNode::reconstructRealTree` causing index collisions.


## v1.0

The initial release.
