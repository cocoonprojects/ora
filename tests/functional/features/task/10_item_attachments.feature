Feature: Items attachments
	As an item participant
	I want to attach some deliverables to the item
	in order to better describe the results obtained from that item

Scenario: Successfully creating a decision item
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Task"
    And that its "attachments" is "_json_"
    """
[{
 "id": "1SlCmMlYt4HhJidJrvGhIVDJ7bwHKhHbGy-eWqT_iqac",
 "serviceId": "doc",
 "mimeType": "application/vnd.google-apps.document",
  "name": "Post Mongo is great",
 "description": "",
 "type": "document",
 "lastEditedUtc": 1461705534029,
 "iconUrl": "https://ssl.gstatic.com/docs/doclist/images/icon_11_document_list.png",
 "url": "https://docs.google.com/document/d/1SlCmMlYt4HhJidJrvGhIVDJ7bwHKhHbGy-eWqT_iqac/edit?usp=drive_web",
 "embedUrl": "https://docs.google.com/document/d/1SlCmMlYt4HhJidJrvGhIVDJ7bwHKhHbGy-eWqT_iqac/preview",
 "driveSuccess": true,
 "sizeBytes": 0
 }]
    """
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000004/attachments"
    Then the response status code should be 200
    And the response should have a "attachments[0].name" property
    And the "attachments[0].name" property should be "Post Mongo is great"
