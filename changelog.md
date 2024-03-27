
# Changelog

> 📖 back to [readme](readme.md)

Oliva follows semantic versioning.\
Please report any issues.


## v1.1.x

- Added `Seed::chain` method for iterable collection chaining: the new method takes over `Seed::merged` which becomes its alias for backward compatibility.
- Deprecated `Seed::merged`, use `Seed::chain` instead.
- Fixed repeated calls to `ShadowNode::reconstructRealTree` causing index collisions.


## v1.0

The initial release.
