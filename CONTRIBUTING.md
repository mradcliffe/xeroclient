# Contributing to xeroclient

Thank you for your interest in xeroclient! Only with all of our help can we improve and move open source software forward.

## Goal

The goal of xeroclient (the software project) is to provide a clean, framework-agnostic PHP library to interface with the [Xero API](https://developer.xero.com/).

## Code of Conduct

* We shall be respectful when we post in the issue queue, pull request queue, in our code, in commit messages. Communication that is disrespectful on the basis of race, gender, sexual orientation, religion, ethnicity, language, age, physical capabilities, technical skill, or contribution experience are not welcome on this project.
* We shall welcome contribution and recognize that the most valuable contributions come from those of us from different backgrounds and experiences. Note that accounting is a global industry with different complexities depending on the locality in which the software is used.
* We shall try to reach a consensus on decisions in the issue queue and recognize that some times a decision cannot be unanimously reached. The project maintainer will make a decision based on the current state of an issue or pull request.
* We shall give attribution to code and non-code contributions.
* We shall respect those who fork the repository and adapt xeroclient to their own needs.

## Accountability

* "mradcliffe" will make decisions regarding code and software architecture underneath the `mradcliffe/xeroclient`. A fork maintainer is responsible for their actions underneath their own remote repository.
* "mradcliffe" will close and delete any comment that does not adhere to the Code of Conduct. Code and commit messages that do not adhere to the Code of Conduct cannot be modified, but should be addressed publically.
* Behavior that is unacceptable by "mradcliffe" can and should be reported to the Drupal Community Working Group even though this project is not related to Drupal. This is the closest governing body to address accountability for "mradcliffe" as they have leadership roles in the Drupal community.

## Getting Started

See the [README](./README.md) for usage and installation instructions.

### Reporting an issue

Reporting an issue is one of the most important means of contribution. An issue can be a

1. **task** - an actionable "thing" that needs to be done on the project such as "Improve test coverage for private application work flow".
2. **bug** - a report of non-working behavior related to the project and how you integrate with your appplication such as "Unable to use on PHP 7.3".
3. **feature** - a nice-to-have that would improve developer experience when integrating the project with your application such as "Add an Exception class to isolate certificate expiration errors".

Issue titles are usually respectful *imperatives*, and should not indicate urgency or priority.

1. Search the issue queue for one or more key words that may be related to a bug, task, or feature that you are interested in.
2. Create the issue using the [issue template](./.github/ISSUE_TEMPLATE/issue_template.md).

### Contributing to an issue

1. Fork the repository on GitHub.
2. Add your remote repository. The following are some suggested terminal commands:
   1. `git clone git@github.com:USERNAME/xeroclient.git`
   2. `git remote add upstream git@github.com:mradcliffe/xeroclient.git`
   3. `git fetch -p --all`
   4. `git checkout -b issue-NUM`
3. Read the issue summary
4. Post a comment on the issue and be specific about what you are going to work on. If you are working together with someone make sure that each person posts a comment on the issue.
   1. Is there an actionable task on the proposed resolution?
       * If no, share/propose a resolution based on reading the issue summary and the issue comments.
   2. Are there any pending questions or tasks in the comments?
       * If yes, share/propose an answer to those questions.
   3. Do you have any questions?
       * If yes, share this in a comment.
   4. Can you manually test the issue?
       * If yes, please describe in your comment what you did to manually test the issue, and if relevant, include screenshots in your comment.
5. Make the changes to the project and commit it. Your commit message should begin with a third-person verb followed by a short description and the issue number.
6. Create a Pull Request for the issue.
7. Respond to review comments. We are all busy people, and it takes time for us to review and respond.

### Contributing to a pull request

A pull request (PR) can be **active**, **inactive**, or **merged**.

An active PR is a open PR that someone (the PR owner) has contributed to "recently". You should make a comment and ask to collaborate on the PR owner's remote repository. You should make your own PR into the PR owner's remote repository, and the PR owner should use a "Squash and Merge" strategy when merging the PR.

An inactive PR is a open or closed PR that has not been contributed to "recently". You should make a comment that you will continue the work in your own PR. You can add the PR owner's remote repository and create a branch based off of their work, and continue their work.

A merged PR should not be re-opened or worked on. A new issue should be created if there is a new feature, bug, or task related to that PR.

### Closing a Pull Request

A pull request may be closed when it is inactive or ready to merge.
