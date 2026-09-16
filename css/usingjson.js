fetch("understandingjson.json")git add
    .then(response => response.json())
    .then(data => {
        console.log("Name: " + data.name);
        console.log("Age: " + data.age);
        console.log("Skills: " + data.skills.join(", "));
    });
