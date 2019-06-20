# TYPO3.Fluid Rendering Engine

## Contribution workflow

First of all, clone this repository.

As we use the Gitflow standard for the developing and maintenance phase, you need to have it installed (preinstalled e.g. in SourceTree, Tower, GitKraken, or other tools as well / [manual installation instructions](https://github.com/petervanderdoes/gitflow-avh/wiki/Installation)) and the branching model looks like:

![Gitflow for Fluid](assets/GitFlow_Fluid.svg)

(This was created with [https://www.draw.io](https://www.draw.io) and could be modified with the [GitFlow_Fluid.xml](assets/GitFlow_Fluid.xml))

After you have cloned the reposiotry you have to initialize the Gitflow and define the develop branch, as this is not the default (which would be `develop`).

Initialize via GUI-Client or via CLI:

```
git flow init
```

You are also able to modify the `.git/config` file manually after you have initialized the Gitflow.
It should look like, but it depends on the version you are working on:

```
[core]
	repositoryformatversion = 0
	filemode = flase
	bare = false
	logallrefupdates = true
[remote "origin"]
	url = {path/to/repo}
	fetch = +refs/heads/*:refs/remotes/origin/*
[branch "master"]
	remote = origin
	merge = refs/heads/master
[gitflow "branch"]
	master = master
	develop = 3.x-dev
[gitflow "prefix"]
	feature = feature/
	bugfix = bugfix/
	release = release/
	hotfix = hotfix/
	support = support/
	versiontag = 
```